<?php

namespace App\Models;

class SecurityQuestion extends BaseModel
{
    protected string $table = 'security_questions';

    protected array $fillable = [
        'question',
        'is_active'
    ];

    public function getActiveQuestions(): array
    {
        return $this->all(['is_active' => 1]);
    }

    /**
     * Obter perguntas e respostas de um utilizador (para verificação)
     */
    public function getUserQuestions(int $userId): array
    {
        $sql = "SELECT sq.id, sq.question, usa.answer_hash 
                FROM user_security_answers usa
                JOIN security_questions sq ON sq.id = usa.question_id
                WHERE usa.user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Salvar respostas do utilizador (apaga anteriores)
     */
    public function saveUserAnswers(int $userId, array $answers): void
    {
        $this->db->beginTransaction();
        try {
            // Limpar respostas anteriores
            $del = $this->db->prepare("DELETE FROM user_security_answers WHERE user_id = :uid");
            $del->execute(['uid' => $userId]);

            // Inserir novas
            $ins = $this->db->prepare("INSERT INTO user_security_answers (user_id, question_id, answer_hash) VALUES (:uid, :qid, :hash)");

            foreach ($answers as $ans) {
                // Normalização: lowercase, trim
                $normalized = mb_strtolower(trim($ans['answer']));
                $hash = password_hash($normalized, PASSWORD_DEFAULT);

                $ins->execute([
                    'uid' => $userId,
                    'qid' => $ans['question_id'],
                    'hash' => $hash
                ]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
