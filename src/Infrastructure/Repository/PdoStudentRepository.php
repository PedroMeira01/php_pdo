<?php

namespace Alura\Pdo\Infrastructure\Repository;

use PDO;
use DateTimeInterface;
use Alura\Pdo\Domain\Model\Student; 
use Alura\Pdo\Domain\Repository\StudentRepository;
use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;
use DateTimeImmutable;
use PDOStatement;

class PdoStudentRepository implements StudentRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = ConnectionCreator::createConnection();
    }

    public function save(Student $student): bool
    {
        /**
         * Método que verifica se o objeto possui ID definido, se não houver
         * insere, pois significa que o objeto não foi inserido no banco.
         * Se houver ID, significa que já foi inserido, então atualize.
         * 
         * @param Student $student - objeto da classe Student
         * 
         * @return bool - retorna a resposta do banco.
         */
        
        if ($student->id() == null) {
            return $this->insert($student);
        }

        return $this->update($student);
        
    }

    public function allStudents(): array
    {
        $query = "SELECT * FROM students";
        $statement = $this->connection->query($query);

        return $this->hydrateStudentList($statement);

    }

    public function studentBirthAt(DateTimeInterface $birthDate): array
    {
        $query = "SELECT * FROM students WHERE birth_date = ?;";
        $statement = $this->connection->prepare($query);
        $statement->bindValue(1, $birthDate->format('Y-m-d'));
        $statement->execute();

        return $this->hydrateStudentList($statement);
    }

    public function hydrateStudentList(PDOStatement $statement): array
    {
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $students = [];

        foreach ($data as $student) {
            $students[] = new Student(
                $student['id'],
                $student['name'],
                new DateTimeImmutable($student['birth_date'])
            );
        }

        return $students;
    }

    public function insert(Student $student): bool
    {
        $query = "INSERT INTO students (name, birth_date) VALUES (:name, :birth_date);";
        $statement = $this->connection->prepare($query);
        $response = $statement->execute([
            ':name' => $student->name(),
            ':birth_date' => $student->birthDate()
        ]);

        $student->defineId($this->connection->lastInsertId());

        return $response;
    }

    public function update(Student $student): bool
    {
        $query = "UPDATE students SET name = :name, birth_date = :birth_date WHERE id = :id;";
        $statement = $this->connection->prepare($query);
        $statement->bindValue(':name', $student->name());
        $statement->bindValue(':birth_date', $student->birthDate());
        $statement->bindValue(':id', $student->id());
        
        return $statement->execute();
    }

    public function remove(Student $student): bool
    {
        $statement = $this->connection->prepare("DELETE FROM students WHERE id = ?");
        $statement->prepare(1, $student->id());
        
        return $statement->execute();
    }
}