<?php


namespace Mleczek\Xml\Tests\Fixtures;


use Mleczek\Xml\Xmlable;
use Mleczek\Xml\XmlTransformation;

class User implements Xmlable
{
    use XmlTransformation;

    public $id = 5;
    public $first_name = 'John';
    public $last_name = 'Smith';
    public $sex = 'male';

    public function xml()
    {
        return [
            'user' => [
                '@id' => 'id',
                'name' => [
                    'first' => 'first',
                    'last' => 'last',
                    'full' => "={$this->first_name} {$this->last_name}",
                ],
                'gender' => 'sex'
            ]
        ];
    }
}