<?php

namespace App\DataFixtures;
use App\Entity\Mascota;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MascotaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i < 11; $i++) {
            $mascota = new Mascota();
            $mascota->setNombre('Carlitos');
            $mascota->setEspecie('Perro');
            $mascota->setEdad(5);
            $mascota->setTamaño('Grande');
            $mascota->setDescripcion('Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum....');
            $mascota->setImagen("https://images.unsplash.com/photo-1518020382113-a7e8fc38eac9?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fGRvZ3xlbnwwfHwwfHx8MA%3D%3D");
            $mascota->setDisponible(1);
            $manager->persist($mascota);
            }

        $manager->flush();
    }
}
