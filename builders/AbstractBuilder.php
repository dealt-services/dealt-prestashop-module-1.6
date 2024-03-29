<?php

abstract class AbstractBuilder
{
    abstract public function build($id=null);
    abstract public function createOrUpdate($data);
}