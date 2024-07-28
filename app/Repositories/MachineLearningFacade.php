<?php

namespace App\Repositories;

interface MachineLearningFacade
{
    public function getPersonality($features): string;

    public function getLimit($features): float;
}
