<?php

namespace Tests;

class FixtureDataTool
{
    public function getData(string $name, string $path = '/tests/fixtures/')
    {
        $data = file_get_contents(dirname(__DIR__) . $path . $name);
        return $this->base64Unserialize($data);
    }

    private function base64Unserialize(string $string)
    {
        return unserialize(base64_decode($string));
    }


}