<?php

use Alura\Pdo\Domain\Model\Student;

require_once 'vendor/autoload.php';

$student = new Student(
    null,
    'Pedro Meira',
    new \DateTimeImmutable('2001-03-29')
);

echo $student->age();
