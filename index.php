<?php

$dbh = new PDO('mysql:host=localhost;dbname=php-quiz-plus', 'root', 'root');

class Question
{
    private int $id;
    private string $description;
    private int $rank;
    private int $rightAnswerId;
    private int $quizId;

    public static function findAll()
    {
        global $dbh;
        $statement = $dbh->query("SELECT * FROM `questions`");
        $result = $statement->fetchAll(PDO::FETCH_FUNC, 'Question::instantiateFromData');
        return $result;
    }

    public static function findById(int $id)
    {
        global $dbh;
        $statement = $dbh->prepare("SELECT * FROM `questions` WHERE `id` = :id");
        $statement->execute([ ':id' => $id ]);
        $result = $statement->fetchAll(PDO::FETCH_FUNC, 'Question::instantiateFromData');
        if (empty($result)) {
            return null;
        } else {
            return $result[0];
        }
    }

    public static function findByRankAndQuiz(int $rank, int $quizId)
    {
        global $dbh;
        $statement = $dbh->prepare("SELECT * FROM `questions` WHERE `rank` = :rank AND `quiz_id` = :quizId");
        $statement->execute([ ':rank' => $rank, ':quizId' => $quizId ]);
        $result = $statement->fetchAll(PDO::FETCH_FUNC, 'Question::instantiateFromData');
        if (empty($result)) {
            return null;
        } else {
            return $result[0];
        }
    }

    public static function instantiateFromData(...$params)
    {
        return new Question(...$params);
    }

    public function update()
    {
        global $dbh;
        $statement = $dbh->prepare("UPDATE `questions` SET `description` = :description, `rank` = :rank, `right_answer_id` = :rightAnswerId, `quiz_id` = :quizId WHERE `id` = :id");
        $statement->execute([
            ':description' => $this->description,
            ':rank' => $this->rank,
            ':rightAnswerId' => $this->rightAnswerId,
            ':quizId' => $this->quizId,
            ':id' => $this->id,
        ]);
    }

    public function delete()
    {
        global $dbh;
        $statement = $dbh->prepare("DELETE FROM `questions` WHERE `id` = :id");
        $statement->execute([ ':id' => $this->id ]);
    }

    public function __construct($id = 0, $description = '', $rank = 0, $rightAnswerId = 0, $quizId = 0)
    {
        $this->id = $id;
        $this->description = $description;
        $this->rank = $rank;
        $this->rightAnswerId = $rightAnswerId;
        $this->quizId = $quizId;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of rank
     */ 
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set the value of rank
     *
     * @return  self
     */ 
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get the value of rightAnswerId
     */ 
    public function getRightAnswerId()
    {
        return $this->rightAnswerId;
    }

    /**
     * Set the value of rightAnswerId
     *
     * @return  self
     */ 
    public function setRightAnswerId($rightAnswerId)
    {
        $this->rightAnswerId = $rightAnswerId;

        return $this;
    }

    /**
     * Get the value of quizId
     */ 
    public function getQuizId()
    {
        return $this->quizId;
    }

    /**
     * Set the value of quizId
     *
     * @return  self
     */ 
    public function setQuizId($quizId)
    {
        $this->quizId = $quizId;

        return $this;
    }
}


function deleteQuestion(int $id)
{
    // Récupère la question en BDD
    $question = Question::findById($id);
    // Si la question n'existe pas
    if ( is_null($question) ) {
        // Erreur 404
        http_response_code(404);
        return;
    }
    // Supprime la question de la BDD
    $question->delete();
    // Lire le rang de la queston demandée → i
    $i = $question->getRank();
    // Crée une boucle infinie
    while (true) {
        // Récupère la question de rang i + 1 en base de données
        $currentQuestion = Question::findByRankAndQuiz( $i + 1, $question->getQuizId() );
        // Si la queston n'existe pas
        if ( is_null($currentQuestion) ) {
            // Interrompt la fonction
            return;
        }
        // Décrémente le rang de la queston de 1
        $currentQuestion->setRank( $currentQuestion->getRank() - 1 );
        // Enregistre la question en BDD
        $currentQuestion->update();
        // Incrémente i de 1
        $i += 1;
    }
}

deleteQuestion(3);
