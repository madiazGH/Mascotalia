<?php

namespace App\Repository;

use App\Entity\Mascota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mascota>
 */
class MascotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mascota::class);
    }

    //    /**
    //     * @return Mascota[] Returns an array of Mascota objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Mascota
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

        public function buscarMascotasConSolicitudes()
        {
            return $this->createQueryBuilder('m')
                ->innerJoin('m.solicitudes', 's') 
                ->groupBy('m.id') 
                ->getQuery()
                ->getResult();
        }

        public function buscarConFiltros($especie, $tamano, $edad, $orden) 
        {
            $qb = $this->createQueryBuilder('m')
                ->where('m.disponible = :disponible')
                ->setParameter('disponible', true);

            if ($especie) {
                $qb->andWhere('m.especie = :especie')
                ->setParameter('especie', $especie);
            }

            if ($tamano) {
                $qb->andWhere('m.tamano = :tamano') 
                ->setParameter('tamano', $tamano);
            }

            if ($edad) {
                $qb->andWhere('m.edad = :edad')
                ->setParameter('edad', $edad);
            }

            // ordenamiento
            switch ($orden) {
                case 'antigua':
                    $qb->orderBy('m.id', 'ASC'); // ID más bajo = Más vieja
                    break;
                default: // 'reciente' o null
                    $qb->orderBy('m.id', 'DESC'); // ID más alto = Más nueva
                    break;
            }

            return $qb->getQuery()->getResult();
        }

        public function buscarParaAdmin($especie, $tamano, $edad, $estado, $orden) 
        {
            $qb = $this->createQueryBuilder('m');

            if ($especie) {
                $qb->andWhere('m.especie = :especie')->setParameter('especie', $especie);
            }
            if ($tamano) {
                $qb->andWhere('m.tamano = :tamano')->setParameter('tamano', $tamano);
            }
            if ($edad) {
                $qb->andWhere('m.edad = :edad')->setParameter('edad', $edad);
            }
            // filtro para Admin: Estado (Disponible o No)
            if ($estado !== null && $estado !== '') {
                $esDisponible = ($estado === '1'); // Convertimos "1" a true, "0" a false
                $qb->andWhere('m.disponible = :disp')->setParameter('disp', $esDisponible);
            }

            // ordenamiento
            if ($orden === 'mas_solicitudes') {
                $qb->leftJoin('m.solicitudes', 's')
                ->addSelect('COUNT(s.id) as HIDDEN req_count')
                ->groupBy('m.id')
                ->orderBy('req_count', 'DESC'); // Mayor a menor
                
            } elseif ($orden === 'menos_solicitudes') { 
                $qb->leftJoin('m.solicitudes', 's')
                ->addSelect('COUNT(s.id) as HIDDEN req_count')
                ->groupBy('m.id')
                ->orderBy('req_count', 'ASC'); // Menor a mayor 
                
            } elseif ($orden === 'antigua') {
                $qb->orderBy('m.id', 'ASC');
            } else {
                // Default: Reciente
                $qb->orderBy('m.id', 'DESC');
            }

            return $qb->getQuery()->getResult();
        }

        public function buscarSolicitadasConFiltros($especie, $tamano, $orden) 
        {
            $qb = $this->createQueryBuilder('m')
                ->innerJoin('m.solicitudes', 's')
                ->groupBy('m.id');

            // Filtros 
            if ($especie) {
                $qb->andWhere('m.especie = :especie')->setParameter('especie', $especie);
            }
            if ($tamano) {
                $qb->andWhere('m.tamano = :tamano')->setParameter('tamano', $tamano);
            }

            // ordenamiento
            $qb->addSelect('COUNT(s.id) as HIDDEN req_count'); // para popularidad

            switch ($orden) {
                case 'mas_solicitudes':
                    $qb->orderBy('req_count', 'DESC');
                    break;
                case 'menos_solicitudes':
                    $qb->orderBy('req_count', 'ASC');
                    break;
                    
                // mas antiguas
                case 'antigua':
                    // Buscamos la solicitud más vieja (MIN id) de cada mascota
                    // y mostramos primero las que tengan fechas más antiguas
                    $qb->addSelect('MIN(s.id) as HIDDEN min_sol_id')
                    ->orderBy('min_sol_id', 'ASC');
                    break;

                default: // 'reciente'
                    // Solicitud más nueva (MAX id) primero
                    $qb->addSelect('MAX(s.id) as HIDDEN max_sol_id')
                    ->orderBy('max_sol_id', 'DESC');
                    break;
            }

            return $qb->getQuery();
        }
}
