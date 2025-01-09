<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JourneyControllerTest extends TestCase
{
    /**
     * Test para un vuelo directo válido.
     */
    public function testDirectFlight()
    {
        $response = $this->json('GET', '/api/journeys/search', [
            'date' => '2024-09-12',
            'from' => 'BUE',
            'to' => 'MAD',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Viajes encontrados.',
            'journeys' => [
                [
                    'connections' => 1,
                    'path' => [
                        [
                            'flight_number' => 'XX1234',
                            'from' => 'BUE',
                            'to' => 'MAD',
                            'departure_time' => '2024-09-12 12:00:00',
                            'arrival_time' => '2024-09-12 23:59:59',
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Test para un viaje con conexión válida.
     */
    public function testConnectingFlights()
    {
        $response = $this->json('GET', '/api/journeys/search', [
            'date' => '2024-09-12',
            'from' => 'BUE',
            'to' => 'PMI',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Viajes encontrados.',
            'journeys' => [
                [
                    'connections' => 2,
                    'path' => [
                        [
                            'flight_number' => 'XX1234',
                            'from' => 'BUE',
                            'to' => 'MAD',
                            'departure_time' => '2024-09-12 12:00:00',
                            'arrival_time' => '2024-09-12 23:59:59',
                        ],
                        [
                            'flight_number' => 'XX2345',
                            'from' => 'MAD',
                            'to' => 'PMI',
                            'departure_time' => '2024-09-13 02:00:00',
                            'arrival_time' => '2024-09-13 03:00:00',
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Test para un viaje sin resultados.
     */
    public function testNoFlightsFound()
    {
        $response = $this->json('GET', '/api/journeys/search', [
            'date' => '2021-12-31',
            'from' => 'NYC',
            'to' => 'MAD',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'No se encontraron viajes para los criterios especificados.',
        ]);
    }

    /**
     * Test para validación de campos obligatorios.
     */
    public function testValidationErrors()
    {
        $response = $this->json('GET', '/api/journeys/search', [
            'from' => 'BUE',
            'to' => 'MAD',
        ]);

        $response->assertStatus(422); // Error de validación
        $response->assertJsonValidationErrors(['date']);
    }
}
