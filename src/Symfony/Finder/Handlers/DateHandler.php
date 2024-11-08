<?php

namespace Src\Symfony\Finder\Handlers;

use Carbon\Carbon;
use SplFileInfo;

class DateHandler extends FilterHandler
{
    public function __construct(
        \Iterator $iterator,
        protected array $dates = []
    ) {
        parent::__construct($iterator);
    }
    protected function accept(SplFileInfo $file): bool
    {
        if (!file_exists($file->getPathname())) {
            return false;
        }

        $fileDate = Carbon::parse($file->getMTime());
        foreach ($this->dates as $date) {
            [$operation, $value] = $date;
            $value = Carbon::parse($value);
            if (str_contains($operation, "<") && $fileDate->lte($value)) {
                return false;
            }
            if (str_contains($operation, ">") && $fileDate->gte($value)) {
                return false;
            }
        }

        return true;
    }
}
