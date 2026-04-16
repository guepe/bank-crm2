<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ListFilterOptions
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return list<string>
     */
    public function distinctNonEmptyValues(string $entityClass, string $alias, string $field): array
    {
        $results = $this->entityManager->createQueryBuilder()
            ->select(sprintf('DISTINCT %s.%s AS value', $alias, $field))
            ->from($entityClass, $alias)
            ->andWhere(sprintf('%s.%s IS NOT NULL', $alias, $field))
            ->andWhere(sprintf("TRIM(%s.%s) <> ''", $alias, $field))
            ->orderBy(sprintf('%s.%s', $alias, $field), 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $row): string => (string) $row['value'],
            $results
        );
    }
}
