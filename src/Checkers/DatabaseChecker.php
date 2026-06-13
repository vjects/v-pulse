<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\DB;

class DatabaseChecker extends BaseChecker
{
    public function getName(): string
    {
        return $this->tr('db_name');
    }

    public function getDescription(): string
    {
        return $this->tr('db_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return true; // Always applicable
    }

    public function run(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'success' => true,
                'message' => $this->tr('db_ok')
            ];
        } catch (\Exception $e) {
            $msg = $this->tr('db_fail', ['error' => $e->getMessage()]);
            if ($this->isLocal()) {
                $msg .= $this->tr('db_local_note');
            }
            return [
                'success' => false,
                'message' => $msg
            ];
        }
    }
}
