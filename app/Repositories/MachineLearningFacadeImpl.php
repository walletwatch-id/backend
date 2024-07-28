<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;

class MachineLearningFacadeImpl implements MachineLearningFacade
{
    public function getPersonality($features): string
    {
        $result = Http::runpod()
            ->post('/runsync', [
                'input' => [
                    'model_id' => 'personality',
                    'features' => [
                        'f01' => $features['f01'],
                        'f02' => $features['f02'],
                        'f03' => $features['f03'],
                        'f04' => $features['f04'],
                        'f05' => $features['f05'],
                        'f06' => $features['f06'],
                        'f07' => $features['f07'],
                        'f08' => $features['f08'],
                        'f09' => $features['f09'],
                        'f10' => $features['f10'],
                        'f11' => $features['f11'],
                        'f12' => $features['f12'],
                        'f13' => $features['f13'],
                        'f14' => $features['f14'],
                        'f15' => $features['f15'],
                        'f16' => $features['f16'],
                        'f17' => $features['f17'],
                        'f18' => $features['f18'],
                        'f19' => $features['f19'],
                        'f20' => $features['f20'],
                        'f21' => $features['f21'],
                        'f22' => $features['f22'],
                        'f23' => $features['f23'],
                        'f24' => $features['f24'],
                        'f25' => $features['f25'],
                        'f26' => $features['f26'],
                        'f27' => $features['f27'],
                        'f28' => $features['f28'],
                        'f29' => $features['f29'],
                        'f30' => $features['f30'],
                        'f31' => $features['f31'],
                        'f32' => $features['f32'],
                        'f33' => $features['f33'],
                        'f34' => $features['f34'],
                        'f35' => $features['f35'],
                        'f36' => $features['f36'],
                    ],
                ],
            ])
            ->object();

        return $result->output;
    }

    public function getLimit($features): float
    {
        $result = Http::runpod()
            ->post('/runsync', [
                'input' => [
                    'model_id' => 'limit',
                    'features' => [
                        'total_income' => $features['total_income'],
                        'total_installment' => $features['total_installment'],
                        'personality' => $features['personality'],
                        'last_month_limit' => $features['last_month_limit'],
                    ],
                ],
            ])
            ->object();

        return $result->output;
    }
}
