<?php

namespace App\Console\Commands;

use App\Models\Warranty;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixWarrantyCustomerNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warranty:fix-customer-names {--dry-run : Run without making actual changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix incorrect customer_name and project_name in warranties table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting warranty customer name fix...');

        $warranties = Warranty::with([
            'dispatch.project.customer',
            'dispatch.rental.customer'
        ])->get();

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $this->output->progressStart($warranties->count());

        foreach ($warranties as $warranty) {
            try {
                $changes = $this->processWarranty($warranty, $isDryRun);

                if ($changes) {
                    $updated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error processing warranty {$warranty->warranty_code}: " . $e->getMessage());
                Log::error("Fix warranty customer names error", [
                    'warranty_code' => $warranty->warranty_code,
                    'error' => $e->getMessage()
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->newLine();
        $this->info("✅ Completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total warranties', $warranties->count()],
                ['Updated', $updated],
                ['Skipped (no change needed)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($isDryRun && $updated > 0) {
            $this->warn('⚠️  This was a dry run. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }

    private function processWarranty(Warranty $warranty, bool $isDryRun): bool
    {
        $dispatch = $warranty->dispatch;

        if (!$dispatch) {
            return false;
        }

        $newCustomerName = null;
        $newProjectName = null;

        if ($dispatch->dispatch_type === 'rental' && $dispatch->rental) {
            // Cho phiếu thuê: lấy từ rental
            $rental = $dispatch->rental;
            $newProjectName = $rental->rental_name ?? 'Cho thuê';

            // Load customer relationship nếu chưa có
            if (!$rental->relationLoaded('customer')) {
                $rental->load('customer');
            }

            // Lấy tên khách hàng từ customer relationship
            if ($rental->customer) {
                $customer = $rental->customer;
                $companyName = $customer->company_name ?? '';
                $representativeName = $customer->name ?? '';

                if ($companyName && $representativeName) {
                    $newCustomerName = $companyName . ' (' . $representativeName . ')';
                } elseif ($companyName) {
                    $newCustomerName = $companyName;
                } elseif ($representativeName) {
                    $newCustomerName = $representativeName;
                }
            }

        } elseif ($dispatch->project) {
            // Cho phiếu dự án: lấy từ project và customer
            $project = $dispatch->project;
            $newProjectName = $project->project_name;

            if ($project->customer) {
                $customer = $project->customer;
                $companyName = $customer->company_name ?? '';
                $representativeName = $customer->name ?? '';

                if ($companyName && $representativeName) {
                    $newCustomerName = $companyName . ' (' . $representativeName . ')';
                } elseif ($companyName) {
                    $newCustomerName = $companyName;
                } elseif ($representativeName) {
                    $newCustomerName = $representativeName;
                }
            }
        }

        // Kiểm tra xem có cần update không
        $needsUpdate = false;
        $updates = [];

        if ($newCustomerName && $warranty->customer_name !== $newCustomerName) {
            $needsUpdate = true;
            $updates['customer_name'] = [
                'old' => $warranty->customer_name,
                'new' => $newCustomerName
            ];
        }

        if ($newProjectName && $warranty->project_name !== $newProjectName) {
            $needsUpdate = true;
            $updates['project_name'] = [
                'old' => $warranty->project_name,
                'new' => $newProjectName
            ];
        }

        if (!$needsUpdate) {
            return false;
        }

        // Log changes
        $this->newLine();
        $this->line("📝 Warranty: <info>{$warranty->warranty_code}</info>");

        foreach ($updates as $field => $change) {
            $this->line("   {$field}:");
            $this->line("      <fg=red>- {$change['old']}</>");
            $this->line("      <fg=green>+ {$change['new']}</>");
        }

        // Apply changes if not dry run
        if (!$isDryRun) {
            if (isset($updates['customer_name'])) {
                $warranty->customer_name = $updates['customer_name']['new'];
            }
            if (isset($updates['project_name'])) {
                $warranty->project_name = $updates['project_name']['new'];
            }
            $warranty->save();
        }

        return true;
    }
}
