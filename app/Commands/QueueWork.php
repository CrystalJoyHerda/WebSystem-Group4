<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\JobModel;

class QueueWork extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:work';
    protected $description = 'Process queued background jobs from the jobs table.';

    public function run(array $params)
    {
        $once = in_array('--once', $params, true);
        $max = 10;

        do {
            $processed = $this->workBatch($max);
            if ($once) {
                break;
            }
            if ($processed === 0) {
                sleep(2);
            }
        } while (true);

        return 0;
    }

    private function workBatch(int $max): int
    {
        $model = new JobModel();
        $db = \Config\Database::connect();

        $now = date('Y-m-d H:i:s');

        $jobs = $db->table('jobs')
            ->where('status', 'pending')
            ->groupStart()
                ->where('available_at', null)
                ->orWhere('available_at <=', $now)
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->limit($max)
            ->get()
            ->getResultArray();

        $count = 0;

        foreach ($jobs as $job) {
            $count++;
            $id = (int) $job['id'];

            $attempts = ((int) ($job['attempts'] ?? 0)) + 1;
            if ($attempts > 5) {
                $model->update($id, [
                    'status' => 'failed',
                    'attempts' => $attempts,
                    'last_error' => 'Max attempts exceeded',
                ]);
                CLI::error('Job #' . $id . ' skipped: max attempts exceeded');
                continue;
            }

            $model->update($id, [
                'status' => 'running',
                'attempts' => $attempts,
                'last_error' => null,
            ]);

            try {
                $this->handleJob($job);
                $model->update($id, [
                    'status' => 'done',
                    'last_error' => null,
                ]);
                CLI::write('Job #' . $id . ' done (' . ($job['type'] ?? 'unknown') . ')', 'green');
            } catch (\Throwable $e) {
                $model->update($id, [
                    'status' => 'failed',
                    'last_error' => $e->getMessage(),
                ]);
                CLI::error('Job #' . $id . ' failed: ' . $e->getMessage());
            }
        }

        return $count;
    }

    private function handleJob(array $job): void
    {
        $type = (string) ($job['type'] ?? '');
        $payload = [];

        if (! empty($job['payload_json'])) {
            $decoded = json_decode((string) $job['payload_json'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        if ($type === 'send_email') {
            $to = $payload['to'] ?? null;
            $subject = $payload['subject'] ?? '(no subject)';
            CLI::write('send_email to=' . ($to ?? '(missing)') . ' subject=' . $subject, 'yellow');
            return;
        }

        if ($type === 'generate_report') {
            $reportType = $payload['report_type'] ?? '(unknown)';
            CLI::write('generate_report type=' . $reportType, 'yellow');
            return;
        }

        if ($type === 'directory_sync') {
            $source = $payload['source'] ?? '(unknown)';
            CLI::write('directory_sync source=' . $source, 'yellow');
            return;
        }

        if ($type === 'send_alert') {
            $channel = $payload['channel'] ?? '(unknown)';
            CLI::write('send_alert channel=' . $channel, 'yellow');
            return;
        }

        throw new \RuntimeException('Unknown job type: ' . $type);
    }
}
