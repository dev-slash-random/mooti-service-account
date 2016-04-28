<?php
/*
 *
 * @author Ken Lalobo
 *
 */

namespace Mooti\Service\Account\Model\User;

use Mooti\Framework\Framework;
//use Mooti\Validator\Validator;
use JsonSerializable;

class User implements JsonSerializable
{
    use Framework;

    protected $rules = [
        'id' => [
            'required' => true,
            'type'     => 'number',
            'constraints' => [
                'integer' => true   
            ]
        ],
        'firstName' => [
            'required' => true,
            'type'     => 'string'
        ],
        'lastName' => [
            'required' => true,
            'type'     => 'string'
        ]
    ];

    protected $data = array();

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function validate()
    {
        $validator = $this->createNew(Validator::class);
        
        if ($validator->isValid($this->rules, $this->config) == false) {
            throw new InvalidModelException('The model data is invalid: ' . print_r($validator->getErrors(), 1));
        }
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
