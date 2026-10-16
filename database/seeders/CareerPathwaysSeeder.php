<?php

namespace Database\Seeders;

use App\Models\LearningPath;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starter text for the career-pathway pages. Safe to run on a live site:
 * it only fills fields that are still empty and never overwrites edits made in the admin panel.
 *   php artisan db:seed --class=CareerPathwaysSeeder --force
 * Role descriptions are general industry descriptions; no salaries or placement figures are added.
 */
class CareerPathwaysSeeder extends Seeder
{
    public function run(): void
    {
        $content = [
            'linux' => [
                'intro' => 'For anyone who wants to run the servers behind websites, banks and telecoms. Linux skills are the base for DevOps, cloud and security roles too.',
                'role_text' => 'Linux administrators install, configure and keep servers running: managing users and permissions, storage, networking, services, updates and backups, and automating routine work with shell scripts and Ansible.',
                'skills' => ['Command line and shell scripting', 'Users, permissions and SELinux', 'Storage, LVM and file systems', 'Networking and firewalls', 'System services and troubleshooting', 'Automation with Ansible'],
                'job_titles' => ['Linux System Administrator', 'System Engineer', 'Technical Support Engineer (Linux)', 'Infrastructure Engineer'],
            ],
            'security' => [
                'intro' => 'For people who want to defend organisations from attacks, or test their defences the way attackers would.',
                'role_text' => 'Security analysts monitor systems and respond to alerts and incidents in a security operations centre (SOC). Ethical hackers and penetration testers look for weaknesses in networks, web applications and systems, with permission, and report how to fix them.',
                'skills' => ['Networking and security fundamentals', 'Log analysis and SIEM', 'Incident response', 'Vulnerability assessment', 'Web application and network penetration testing', 'Report writing'],
                'job_titles' => ['SOC Analyst', 'Security Analyst', 'Penetration Tester', 'Vulnerability Assessment Analyst'],
            ],
            'network' => [
                'intro' => 'For people who want to design and run the networks that connect offices, data centres and the internet.',
                'role_text' => 'Network engineers set up and maintain routers, switches, wireless and firewalls; plan IP addressing and routing; and troubleshoot connectivity and performance problems.',
                'skills' => ['TCP/IP and subnetting', 'Switching and VLANs', 'Routing (OSPF, BGP)', 'Network security basics', 'Wireless networking', 'Troubleshooting'],
                'job_titles' => ['Network Engineer', 'Network Administrator', 'NOC Engineer', 'Network Support Engineer'],
            ],
            'devops' => [
                'intro' => 'For people who like both development and operations, and want to ship software quickly and reliably.',
                'role_text' => 'DevOps engineers automate how software is built, tested and deployed, run applications in containers and Kubernetes, and keep production systems observable and secure.',
                'skills' => ['Linux and scripting', 'Git and CI/CD pipelines', 'Docker containers', 'Kubernetes administration', 'Infrastructure as code', 'Monitoring and security'],
                'job_titles' => ['DevOps Engineer', 'Site Reliability Engineer (SRE)', 'Platform Engineer', 'Kubernetes Administrator'],
            ],
            'cloud' => [
                'intro' => 'For people who want to build and run systems on Microsoft Azure, used by many organisations for their cloud services.',
                'role_text' => 'Cloud engineers deploy and manage virtual machines, networks, storage and identity in Azure, design reliable and secure solutions, and automate deployments.',
                'skills' => ['Azure administration', 'Identity and access (Entra ID)', 'Virtual networks and storage', 'Solution architecture', 'Monitoring and cost management', 'Azure DevOps pipelines'],
                'job_titles' => ['Azure Administrator', 'Cloud Engineer', 'Cloud Solutions Architect', 'Azure DevOps Engineer'],
            ],
        ];

        foreach (LearningPath::all() as $path) {
            $c = $content[$path->key] ?? [];
            $path->slug = $path->slug ?: Str::slug($path->name);
            foreach (['intro', 'role_text', 'skills', 'job_titles'] as $field) {
                if (blank($path->{$field}) && isset($c[$field])) {
                    $path->{$field} = $c[$field];
                }
            }
            $path->save();
        }
    }
}
