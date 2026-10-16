<?php

namespace Tests\Feature;

use App\Filament\Resources\Enquiries\Pages\EditEnquiry;
use App\Filament\Resources\TrainingClasses\Pages\EditTrainingClass;
use App\Filament\Resources\TrainingClasses\RelationManagers\StudentsRelationManager;
use App\Filament\Widgets\EnquiryStats;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Student;
use App\Models\TrainingClass;
use App\Models\User;
use Database\Seeders\ContentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentImportSeeder::class);
        $this->actingAs(User::factory()->create());
    }

    private function lead(array $extra = []): Enquiry
    {
        return Enquiry::create($extra + ['form' => 'course', 'name' => 'Test Lead', 'phone' => '9812345678', 'email' => 'lead@example.com', 'course_id' => Course::first()->id, 'audience' => 'Individual', 'consent' => true]);
    }

    public function test_dashboard_and_training_screens_render_empty(): void
    {
        $this->get('/admin')->assertOk()->assertSee('Ongoing classes')->assertSee('Students studying now')
            ->assertSee('Students completed')->assertSee('New leads')->assertSee('No ongoing or upcoming classes');
        foreach (['/admin/classes', '/admin/classes/create', '/admin/students', '/admin/students/create'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_dashboard_counts_classes_students_and_leads(): void
    {
        $course = Course::first();
        $class = TrainingClass::create(['course_id' => $course->id, 'name' => 'RHCSA Morning Demo', 'starts_on' => today()->subWeek(), 'status' => 'ongoing', 'capacity' => 12]);
        TrainingClass::create(['course_id' => $course->id, 'name' => 'Old class', 'starts_on' => today()->subYear(), 'status' => 'completed']);
        Student::create(['name' => 'A', 'course_id' => $course->id, 'training_class_id' => $class->id, 'enrolled_on' => today(), 'status' => 'enrolled']);
        Student::create(['name' => 'B', 'course_id' => $course->id, 'training_class_id' => $class->id, 'enrolled_on' => today(), 'status' => 'enrolled']);
        Student::create(['name' => 'C', 'course_id' => $course->id, 'enrolled_on' => today()->subMonths(2), 'status' => 'completed', 'completed_on' => today(), 'certified' => true]);
        $this->lead();
        $this->lead(['status' => 'contacted', 'name' => 'Second Lead']);

        Livewire::test(EnquiryStats::class)
            ->assertSeeInOrder(['Ongoing classes', '1', 'Students studying now', '2', 'Students completed', '1', '1 certified', 'New leads', '1']);

        $this->get('/admin')->assertOk()->assertSee('RHCSA Morning Demo')->assertSee('2 / 12')->assertSee('Second Lead');
        $this->get('/admin/classes/'.$class->id.'/edit')->assertOk();
        Livewire::test(StudentsRelationManager::class, ['ownerRecord' => $class, 'pageClass' => EditTrainingClass::class])
            ->assertOk()->assertCanSeeTableRecords(Student::where('training_class_id', $class->id)->get());
    }

    public function test_enquiry_can_be_enrolled_as_student(): void
    {
        $lead = $this->lead(['audience' => 'Corporate Batch', 'organisation' => 'Example Ltd']);

        Livewire::test(EditEnquiry::class, ['record' => $lead->getRouteKey()])
            ->callAction('enrol', ['course_id' => $lead->course_id, 'enrolled_on' => today()->toDateString()])
            ->assertHasNoActionErrors();

        $student = Student::sole();
        $this->assertSame(['Test Lead', 'Corporate Batch', 'Example Ltd', 'enrolled', $lead->id], [$student->name, $student->audience, $student->organisation, $student->status, $student->enquiry_id]);
        $this->assertSame('enrolled', $lead->fresh()->status);
    }
}
