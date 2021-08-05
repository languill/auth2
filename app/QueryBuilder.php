<?php

namespace App;

use Aura\SqlQuery\QueryFactory;
use PDO;

class QueryBuilder {
    private $pdo;
    private $queryFactory;

    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo; 
        $this->queryFactory = new QueryFactory('mysql');
    }


    // Пользователи
     public function getAllUsers()
    {
        $select = $this->queryFactory->newSelect();        
        $select->cols(['*'])
            ->from('users')
            ->join(
                'LEFT',             // the join-type
                'user_info AS ui',        // join to this table ...
                'users.id = ui.user_id' // ... ON these conditions
            );
        $sth = $this->pdo->prepare($select->getStatement());
        $sth->execute($select->getBindValues());               
        return $sth->fetchAll(PDO::FETCH_ASSOC);        
    }
    
    public function getAll($table)
    {
        $select = $this->queryFactory->newSelect();        
        $select->cols(['*'])->from($table); 
        $sth = $this->pdo->prepare($select->getStatement());
        $sth->execute($select->getBindValues());               
        return $sth->fetchAll(PDO::FETCH_ASSOC);        
    }

    // Пагинация
    public function getAllPagination($table, $limit, $offset)
    {
        $select = $this->queryFactory->newSelect();        
        $select->cols(['*'])
            ->from($table)
            ->setPaging($limit) // какое кол-во записей на каждой странице
            ->page($offset); // номер страницы

        $sth = $this->pdo->prepare($select->getStatement());
        $sth->execute($select->getBindValues());               
        return $sth->fetchAll(PDO::FETCH_ASSOC);        
    }

    public function getOne($table, $id)
    {
        $select = $this->queryFactory->newSelect();

        $select->cols(['*'])
            ->from($table)
            ->where('id= :id')
            ->bindValue('id', $id);
              
        $sth = $this->pdo->prepare($select->getStatement());
                
        $sth->execute($select->getBindValues());        
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data, $table)
    {
        $insert = $this->queryFactory->newInsert();

        $insert->into($table)             
            ->cols($data);
         

        $sth = $this->pdo->prepare($insert->getStatement());

        $sth->execute($insert->getBindValues());  
    }

    public function update($data, $id, $table)
    {
        $update = $this->queryFactory->newUpdate();

        $update
            ->table($table)                 
            ->cols($data)            
            ->where('id = :id')
            ->bindValue('id', $id);

        $sth = $this->pdo->prepare($update->getStatement());
        
        $sth->execute($update->getBindValues());    
    }

    public function delete($table, $id) 
    {
        $delete = $this->queryFactory->newDelete();

        $delete
            ->from($table)                   
            ->where('id = :id') 
            ->bindValue('id', $id);

        $sth = $this->pdo->prepare($delete->getStatement());
        $sth->execute($delete->getBindValues());    
    }
}