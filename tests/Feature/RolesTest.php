<?php

namespace Tests\Feature;

use App\Filament\Pages\TakeAttendance;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Student;
use App\Models\TrainingClass;
use App\Models\User;
use Database\Seeders\ContentImportSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentImportSeeder::class);
    }

    private function user(string $role, bool $active = true): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => $active]);
    }

    public function test_each_role_sees_only_its_areas(): void
    {
        $matrix = [
            'editor' => ['ok' => ['/admin/posts', '/admin/courses', '/admin/page-content', '/admin/redirects'], 'denied' => ['/admin/enquiries', '/admin/students', '/admin/payments', '/admin/site-settings', '/admin/users']],
            'office' => ['ok' => ['/admin/enquiries', '/admin/students', '/admin/payments', '/admin/classes', '/admin/attendance'], 'denied' => ['/admin/posts', '/admin/site-settings', '/admin/users', '/admin/campaigns']],
            'marketing' => ['ok' => ['/admin/campaigns', '/admin/subscribers', '/admin/email-campaigns', '/admin/enquiries'], 'denied' => ['/admin/payments', '/admin/students', '/admin/users']],
            'manager' => ['ok' => ['/admin/payments', '/admin/posts', '/admin/campaigns'], 'denied' => ['/admin/site-settings', '/admin/users']],
            'admin' => ['ok' => ['/admin/site-settings', '/admin/users', '/admin/users/create'], 'denied' => []],
        ];

        foreach ($matrix as $role => $urls) {
            $this->actingAs($this->user($role));
            $this->get('/admin')->assertOk();
            foreach ($urls['ok'] as $url) {
                $this->get($url)->assertOk();
            }
            foreach ($urls['denied'] as $url) {
                $this->assertContains($this->get($url)->status(), [403, 404], "{$role} should not open {$url}");
            }
        }
    }

    public function test_instructors_only_use_the_trainer_portal(): void
    {
        $this->actingAs($this->user('instructor'));
        $this->get('/admin')->assertForbidden();
        $this->get('/trainer')->assertOk();
        $this->get('/trainer/classes')->assertOk();

        $this->actingAs($this->user('admin', active: false));
        $this->get('/admin')->assertForbidden();
    }

    public function test_instructor_takes_attendance_only_for_own_classes(): void
    {
        $me = $this->user('instructor');
        $other = $this->user('instructor');
        $course = Course::first();
        $mine = TrainingClass::create(['course_id' => $course->id, 'name' => 'My class', 'starts_on' => today()->subWeek(), 'status' => 'ongoing',
            'instructor_user_id' => $me->id, 'days' => array_keys(TrainingClass::DAYS), 'start_time' => '07:00', 'end_time' => '09:00']);
        $theirs = TrainingClass::create(['course_id' => $course->id, 'name' => 'Their class', 'starts_on' => today(), 'status' => 'ongoing', 'instructor_user_id' => $other->id]);
        $a = Student::create(['name' => 'Asha', 'course_id' => $course->id, 'training_class_id' => $mine->id, 'enrolled_on' => today(), 'status' => 'enrolled']);
        $b = Student::create(['name' => 'Bikash', 'course_id' => $course->id, 'training_class_id' => $mine->id, 'enrolled_on' => today(), 'status' => 'enrolled']);
        $c = Student::create(['name' => 'Chandra', 'course_id' => $course->id, 'training_class_id' => $mine->id, 'enrolled_on' => today(), 'status' => 'enrolled']);
        $this->assertSame('Sun–Sat · 7:00 AM–9:00 AM', $mine->fresh()->schedule);

        $this->actingAs($me);
        Filament::setCurrentPanel(Filament::getPanel('trainer'));
        $this->get('/trainer')->assertOk()->assertSee("Today's classes")->assertSee('My class')->assertDontSee('Their class');
        $this->get('/trainer/classes')->assertSee('My class')->assertDontSee('Their class');

        // Table of students; mark two, leave one unmarked (saved as absent). Instructors cannot backdate.
        Livewire::test(TakeAttendance::class, ['classId' => (string) $mine->id])
            ->assertSee('Asha')->assertSee('Bikash')->assertSee('Chandra')->assertSee('Mark all present')
            ->set('data.held_on', today()->subDays(3)->toDateString()) // tampered date: the server still saves today
            ->set("marks.{$a->id}", 'present')
            ->set("marks.{$b->id}", 'late')
            ->set("notes.{$b->id}", 'Bus delay')
            ->set('data.topic', 'LVM basics')
            ->call('save')->assertHasNoErrors();

        $session = ClassSession::sole();
        $this->assertSame([$mine->id, today()->toDateString(), 'LVM basics', $me->id], [$session->training_class_id, $session->held_on->toDateString(), $session->topic, $session->taken_by]);
        $this->assertSame(['present', 'late', 'absent'], [Attendance::where('student_id', $a->id)->value('status'), Attendance::where('student_id', $b->id)->value('status'), Attendance::where('student_id', $c->id)->value('status')]);
        $this->assertSame('Bus delay', Attendance::where('student_id', $b->id)->value('note'));
        $this->assertSame(67, $mine->attendanceRate());

        // Re-opening today loads the saved marks
        Livewire::test(TakeAttendance::class, ['classId' => (string) $mine->id])->assertSet("marks.{$b->id}", 'late')->assertSee('Update attendance');

        // Register: student names with P / L / A
        Livewire::test(\App\Filament\Pages\AttendanceRegister::class, ['classId' => (string) $mine->id])
            ->assertSee('Asha')->assertSee('Chandra')->assertSee('100%')->assertSee('0%');

        // Someone else's batch cannot be saved
        Livewire::test(TakeAttendance::class, ['classId' => (string) $theirs->id])
            ->set('data.training_class_id', $theirs->id)
            ->call('save');
        $this->assertSame(1, ClassSession::count());

        // Admin (training staff) can correct an earlier date
        $this->actingAs($this->user('office'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(TakeAttendance::class, ['classId' => (string) $mine->id, 'dateParam' => today()->subDay()->toDateString()])
            ->call('markAll', 'present')->call('save')->assertHasNoErrors();
        $this->assertSame(2, ClassSession::count());
        $this->assertTrue(ClassSession::whereDate('held_on', today()->subDay())->exists());
        $this->get('/admin/attendance-register?class='.$mine->id)->assertOk()->assertSee('Asha');
        $this->get('/admin/students/'.$a->id.'/edit')->assertOk();
    }
}
