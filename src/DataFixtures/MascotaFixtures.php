<?php

namespace App\DataFixtures;
use App\Entity\Mascota;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MascotaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $edades = ['Cachorro', 'Joven', 'Adulto', 'Abuelo'];
        $tamanos = ['Pequeño', 'Mediano', 'Grande'];

        // Fixture para 6 perros
        for ($i = 1; $i < 7; $i++) {
            $mascotaPerro = new Mascota();
            $mascotaPerro->setNombre('Perro ' . $i);
            
            // Elegimos valores al azar de los arrays
            $mascotaPerro->setEspecie('Perro');
            $mascotaPerro->setEdad($edades[array_rand($edades)]); 
            $mascotaPerro->setTamano($tamanos[array_rand($tamanos)]);
            
            
            $mascotaPerro->setDescripcion('Descripción de prueba...');
            $mascotaPerro->setImagen('perro'.$i.'.jpg');            
            $mascotaPerro->setDisponible(true);
            $manager->persist($mascotaPerro);
        }

        // Fixture para 4 gatos
        for ($i = 1; $i < 5; $i++) {
            $mascotaGato = new Mascota();
            $mascotaGato->setNombre('Gato ' . $i);
            
            // Elegimos valores al azar de los arrays
            $mascotaGato->setEspecie('Gato');
            $mascotaGato->setEdad($edades[array_rand($edades)]); 
            $mascotaGato->setTamano($tamanos[array_rand($tamanos)]);
            
            $mascotaGato->setDescripcion('Descripción de prueba...');
            $mascotaGato->setImagen('gato'.$i.'.jpg');            
            $mascotaGato->setDisponible(true);
            $manager->persist($mascotaGato);
        }
        $manager->flush();
    }
}