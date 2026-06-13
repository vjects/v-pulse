<?php

namespace Vjects\Pulse\Checkers;

interface CheckerInterface
{
    /**
     * The name of the checker (e.g., "Database Connection").
     */
    public function getName(): string;

    /**
     * A brief description of what this checker does.
     */
    public function getDescription(): string;

    /**
     * Check if this checker should run based on the current V-Pulse settings.
     */
    public function isApplicable(array $settings): bool;

    /**
     * Execute the check. Must return an array with 'success' (bool) and 'message' (string).
     */
    public function run(): array;

    /**
     * Get the name of the Action to fix this issue (used by Filament).
     */
    public function getFixActionName(): ?string;

    /**
     * The actual logic to fix the issue if the user clicks the fix action.
     */
    public function executeFix(): void;
}
