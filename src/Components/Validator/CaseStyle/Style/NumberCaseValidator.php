<?php

namespace PHPUnuhi\Components\Validator\CaseStyle\Style;

use PHPUnuhi\Components\Validator\CaseStyle\CaseStyleValidatorInterface;

class NumberCaseValidator implements CaseStyleValidatorInterface
{

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'number';
    }

    /**
     * @param string $text
     * @return bool
     */
    public function isValid(string $text): bool
    {
        return is_numeric($text);
    }
}
