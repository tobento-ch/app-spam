<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\Factory;

return [
    
    /*
    |--------------------------------------------------------------------------
    | Detectors
    |--------------------------------------------------------------------------
    |
    | Configure any detectors needed for your application.
    |
    | See: https://github.com/tobento-ch/app-spam#available-detectors
    |      https://github.com/tobento-ch/app-spam#available-factories
    |
    */
    
    'detectors' => [
        // detectors for forms:
        'default' => new Factory\Composite(
            new Factory\Honeypot(inputName: 'hp'),
            new Factory\MinTimePassed(inputName: 'mtp', milliseconds: 1000),
        ),
        
        'null' => new Detector\NullDetector(name: 'null'),
        
        // detectors for validation rule:
        'email' => new Factory\Composite(
            new Factory\Named('emailDomain'),
            new Factory\Named('emailRemote'),
        ),
        
        'emailDomain' => static function (string $name): DetectorInterface {
            return new Detector\EmailDomain(
                name: $name,
                inputName: '',
                blacklist: [],
                whitelist: [],
            );
        },
        
        'emailRemote' => static function (string $name): DetectorInterface {
            return new Detector\EmailRemote(
                name: $name,
                inputName: '',
                checkDNS: true,
                checkSMTP: true,
                checkMX: true,
                timeoutInSeconds: 5,
            );
        },
        
        'withoutUrl' => new Factory\WithoutUrl(inputNames: []),
    ],
];