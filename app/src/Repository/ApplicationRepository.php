<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class ApplicationRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getApplicationByGrandClient($limit = 10): array
    {
        $sql = "
                SELECT grandClient, application, totalPrix FROM (
                    SELECT
                        gc.NomGrandClient AS grandClient,
                        a.nomAppli        AS application,
                        SUM(lf.prix)      AS totalPrix,
                        ROW_NUMBER() OVER (
                            PARTITION BY gc.NomGrandClient 
                            ORDER BY SUM(lf.prix) DESC
                        ) AS rang
                    FROM grandclients gc
                    LEFT JOIN clients            c ON c.GrandClientID     = gc.GrandClientID
                    LEFT JOIN centresactivite   ca ON ca.CentreActiviteID = c.CentreActiviteID
                    LEFT JOIN ligne_facturation lf ON lf.CentreActiviteID = ca.CentreActiviteID
                    LEFT JOIN application        a ON a.IRT               = lf.IRT
                    GROUP BY gc.NomGrandClient, a.nomAppli
                ) AS classement
                WHERE rang <= :limit
                ORDER BY grandClient, totalPrix DESC;
            ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }


    public function getGrandClient($limit = 5)
    {
        $sql = "
            SELECT gc.GrandClientID
            FROM ligne_facturation lf
            INNER JOIN centresactivite ca ON lf.CentreActiviteID = ca.CentreActiviteID
            INNER JOIN clients c ON c.CentreActiviteID = ca.CentreActiviteID
            INNER JOIN grandclients gc ON gc.GrandClientID = c.GrandClientID
            GROUP BY gc.GrandClientID
            ORDER BY SUM(lf.prix) DESC
            LIMIT :limit;
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();

        $grandClient = $result->fetchAllAssociative();


        $sql2 = "
            SELECT
                gc.NomGrandClient AS grandClient,
                DATE_FORMAT(lf.mois, '%Y-%m') AS mois,
                SUM(lf.prix) AS total_montant
            FROM ligne_facturation lf
            INNER JOIN centresactivite ca ON ca.CentreActiviteID = lf.CentreActiviteID
            INNER JOIN clients          c ON c.CentreActiviteID = ca.CentreActiviteID
            INNER JOIN grandclients    gc ON gc.GrandClientID = c.GrandClientID
            WHERE 
                lf.mois BETWEEN '2021-01-01' AND '2022-04-30'
                AND gc.GrandClientID IN (" . implode(',', array_column($grandClient, 'GrandClientID')).")
            GROUP BY gc.NomGrandClient, YEAR(lf.mois), MONTH(lf.mois)
            ORDER BY gc.NomGrandClient, mois;
        ";

        $stmt = $this->connection->prepare($sql2);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }

}