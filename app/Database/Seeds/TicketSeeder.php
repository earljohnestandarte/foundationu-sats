<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * TicketSeeder — seeds realistic demo tickets across departments (#20).
 * Never runs in production (guarded at top of run()).
 */
class TicketSeeder extends Seeder
{
    public function run()
    {
        // Safety guard
        if (ENVIRONMENT === 'production') {
            echo "TicketSeeder skipped in production.\n";
            return;
        }

        $db = \Config\Database::connect();

        // Resolve user and department IDs dynamically
        $students   = $db->table('users')->where('role', 'student')->get()->getResult();
        $agents     = $db->table('users')->where('role', 'agent')->get()->getResult();
        $departments = $db->table('departments')->get()->getResult();

        if (empty($students) || empty($agents) || empty($departments)) {
            echo "TicketSeeder requires users and departments to be seeded first.\n";
            return;
        }

        // Index agents by department
        $agentsByDept = [];
        foreach ($agents as $agent) {
            if ($agent->department_id) {
                $agentsByDept[$agent->department_id][] = $agent->id;
            }
        }

        $statuses    = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'];
        $priorities  = ['Low', 'Medium', 'High', 'Urgent'];
        $slaMap      = ['Urgent' => 1, 'High' => 4, 'Medium' => 8, 'Low' => 24];
        $concernTypes = [
            'Academic Concern', 'Financial Aid', 'Personal/Counseling',
            'Student Records', 'Campus Life', 'Grievance',
        ];

        $subjects = [
            'Request for Certificate of Enrollment',
            'Grade appeal for midterm examination',
            'Scholarship renewal assistance',
            'Lost ID replacement request',
            'Conflict with dormitory roommate',
            'Request for late enrollment',
            'Assistance with academic probation',
            'Counseling session request',
            'Financial aid documentation missing',
            'Course credit transfer inquiry',
            'Incomplete grade request',
            'Transcript request for employment',
            'Request for endorsement letter',
            'Concern about classroom facilities',
            'Extracurricular club recognition request',
            'Appeal for dropped subject readmission',
            'Request for academic calendar clarification',
            'Dispute with faculty member',
            'Concern about exam scheduling conflict',
            'Request for student verification letter',
        ];

        $descriptions = [
            'I need this document urgently for my scholarship application deadline next week.',
            'There appears to be a discrepancy between my submitted work and the recorded grade.',
            'Please assist me in completing the requirements for my scholarship renewal this semester.',
            'My student ID was lost during the school fair. I need a replacement as soon as possible.',
            'The situation has become uncomfortable and is affecting my studies and well-being.',
            'Due to illness, I was unable to enroll on time. I am requesting special consideration.',
            'I need guidance on how to lift my academic probation status before next semester.',
            'I have been experiencing anxiety and stress and would like to schedule a counseling session.',
            'My submitted documents seem to be missing from the financial aid office records.',
            'I transferred from another university and need my credits properly evaluated.',
        ];

        $tickets = [];
        $now = time();

        foreach ($subjects as $i => $subject) {
            $student    = $students[array_rand($students)];
            $dept       = $departments[array_rand($departments)];
            $priority   = $priorities[array_rand($priorities)];
            $status     = $statuses[array_rand($statuses)];
            $hours      = $slaMap[$priority];
            $created    = date('Y-m-d H:i:s', $now - rand(1, 30) * 86400);
            $sla        = date('Y-m-d H:i:s', strtotime($created) + $hours * 3600);

            $resolverAgents = $agentsByDept[$dept->id] ?? [];
            $resolverId     = $resolverAgents ? $resolverAgents[array_rand($resolverAgents)] : null;

            $resolvedAt      = null;
            $firstResponseAt = null;
            $archivedAt      = null;

            if (in_array($status, ['In Progress', 'Resolved', 'Closed', 'Pending'])) {
                $firstResponseAt = date('Y-m-d H:i:s', strtotime($created) + rand(600, 7200));
            }
            if (in_array($status, ['Resolved', 'Closed'])) {
                $resolvedAt = date('Y-m-d H:i:s', strtotime($created) + rand(86400, 259200));
            }
            if ($status === 'Closed') {
                $archivedAt = date('Y-m-d H:i:s', strtotime($resolvedAt) + rand(3600, 86400));
            }

            $tickets[] = [
                'requester_id'      => $student->id,
                'resolver_id'       => $resolverId,
                'department_id'     => $dept->id,
                'concern_type'      => $concernTypes[array_rand($concernTypes)],
                'subject'           => $subject,
                'description'       => $descriptions[$i % count($descriptions)],
                'status'            => $status,
                'priority'          => $priority,
                'sla_due_at'        => $sla,
                'first_response_at' => $firstResponseAt,
                'resolved_at'       => $resolvedAt,
                'archived_at'       => $archivedAt,
                'is_escalated'      => rand(0, 6) === 0 ? 1 : 0, // ~14% escalated
                'escalated_at'      => null,
                'escalated_by'      => null,
                'escalation_reason' => null,
                'created_at'        => $created,
                'updated_at'        => $created,
            ];
        }

        $db->table('tickets')->insertBatch($tickets);

        echo "TicketSeeder: " . count($tickets) . " demo tickets inserted.\n";
    }
}
