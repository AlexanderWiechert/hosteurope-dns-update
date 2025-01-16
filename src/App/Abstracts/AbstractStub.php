<?php

namespace App\Abstracts;

abstract class AbstractStub
{

    protected static function getArgsOptions() {}

    protected function onBeforeCreate(array $entry) {}

    protected function onAfterCreate(array $entry) {}

    protected function onBeforeUpdate(array $entry) {}

    protected function onAfterUpdate(array $entry) {}

    protected function onBeforeDelete(array $entry) {}

    protected function onAfterDelete(array $entry) {}

}