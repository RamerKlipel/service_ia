<?php

namespace App;

abstract class Migration
{
    abstract public function up(): string;
    abstract public function down(): string;
}
