<?php

namespace App\Common\Fakers;

use Faker\Provider\Base;

class IdentityProvider extends Base
{
    /**
     * Generar DNI español válido (8 números + letra)
     */
    public function dni()
    {
        $number = rand(10000000, 99999999);
        $letters = "TRWAGMYFPDXBNJZSQVHLCKE";
        $letter = $letters[$number % 23];

        return "{$number}{$letter}";
    }

    /**
     * Generar NIE español válido (Empieza con X, Y o Z)
     */
    public function nie()
    {
        $firstLetter = ['X', 'Y', 'Z'][rand(0, 2)];
        $number = rand(1000000, 9999999);

        // Convertir prefijo para cálculo de la letra
        $prefix = ['X' => 0, 'Y' => 1, 'Z' => 2][$firstLetter];

        $fullNumber = $prefix . $number;

        $letters = "TRWAGMYFPDXBNJZSQVHLCKE";
        $letter = $letters[$fullNumber % 23];

        return "{$firstLetter}{$number}{$letter}";
    }

    /**
     * Generar Pasaporte (formato estándar europeo ficticio)
     * 2 letras + 7 números
     */
    public function passport()
    {
        return strtoupper($this->generator->bothify('??#######'));
    }
}
