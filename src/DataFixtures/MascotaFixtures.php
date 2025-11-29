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
        $especies = ['Perro', 'Gato'];

        for ($i = 1; $i < 11; $i++) {
            $mascota = new Mascota();
            $mascota->setNombre('Mascota ' . $i);
            
            // Elegimos valores al azar de los arrays
            $mascota->setEspecie($especies[array_rand($especies)]);
            $mascota->setEdad($edades[array_rand($edades)]); // <--- CAMBIO AQUÍ
            $mascota->setTamaño($tamanos[array_rand($tamanos)]);
            
            // ... resto del código (imagen, descripcion, etc) ...
            $mascota->setDescripcion('Descripción de prueba...');
            $mascota->setImagen("https://images.unsplash.com/photo-1591160690555-5debfba289f0?random=$i");
            $mascota->setDisponible(true);
            
            $manager->persist($mascota);
        }
        $manager->flush();
    }
}