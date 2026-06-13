<?php

namespace Vjects\Pulse\Checkers;

abstract class BaseChecker implements CheckerInterface
{
    /**
     * Default implementation. Can be overridden.
     */
    public function isApplicable(array $settings): bool
    {
        return true;
    }

    /**
     * Default: no fix action.
     */
    public function getFixActionName(): ?string
    {
        return null;
    }

    /**
     * Default: nothing to do.
     */
    public function executeFix(): void
    {
        //
    }
}
