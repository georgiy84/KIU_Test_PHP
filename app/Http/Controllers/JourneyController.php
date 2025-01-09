<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Yaml\Yaml;
use App\Helpers\JsonHelper;

class JourneyController extends Controller
{
    public function search(Request $request)
    {
        // Validar los parametros de entrada
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        try {
            $yamlContent = null;
            $source = config('app.yaml_source'); // Obtener la fuente del YAML
            //Configuracion de YAML_SOURCE permite buscar archivo YAML en diferentes lugares, local, en la nube...
            switch ($source) {
                case 'url':
                    $url = 'https://gitlab.com/kiusys/challenge/-/raw/main/events-api/openapi.yaml';
                    $response = Http::get($url);

                    if ($response->failed()) {
                        return response()->json(['error' => 'No se pudo obtener el archivo YAML desde la URL'], 500);
                    }

                    $yamlContent = $response->body();
                    break;

                case 'local':
                default:
                    $filePath = storage_path('app/flight_events.yaml');
                    if (!file_exists($filePath)) {
                        return response()->json(['error' => 'El archivo YAML no existe en storage/app'], 500);
                    }

                    $yamlContent = file_get_contents($filePath);
                    break;
            }

            if (empty($yamlContent)) {
                return response()->json(['error' => 'El archivo YAML está vacío'], 500);
            }

            $parsedYaml = Yaml::parse($yamlContent);
            $decodedJson = json_decode(json_encode($parsedYaml), true);

            // Buscar eventos de vuelo, usando Helper
            $flightEvents = JsonHelper::findKeyRecursively($decodedJson, 'example');

            if (!$flightEvents || !is_array($flightEvents)) {
                return response()->json(['error' => 'No se encontraron eventos de vuelo en el archivo YAML descargado'], 500);
            }

            // Generar viajes
            $journeys = $this->buildJourneys($flightEvents, $request->input('from'), $request->input('to'), $request->input('date'));

            if (empty($journeys)) {
                return response()->json(['message' => 'No se encontraron viajes para los criterios especificados.'], 404);
            }

            return response()->json(['message' => 'Viajes encontrados.', 'journeys' => $journeys]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar el archivo YAML: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Construir viajes a partir de los eventos de vuelo
     */
    private function buildJourneys(array $events, string $from, string $to, string $date)
{
    $journeys = [];

    \Log::info('Eventos disponibles para procesar:', $events);

    foreach ($events as $firstFlight) {
        // Convertir el timestamp a formato de fecha
        $departureDate = date('Y-m-d', $firstFlight['departure_datetime']);

        // Filtrar vuelos que salen del origen en la fecha dada
        if ($firstFlight['departure_city'] === $from && $departureDate === $date) {
            \Log::info('Vuelo inicial coincidente encontrado:', $firstFlight);

            // Caso 1: Un vuelo directo
            if ($firstFlight['arrival_city'] === $to) {
                $journeys[] = [
                    'connections' => 1,
                    'path' => [
                        [
                            'flight_number' => $firstFlight['flight_number'],
                            'from' => $firstFlight['departure_city'],
                            'to' => $firstFlight['arrival_city'],
                            'departure_time' => date('Y-m-d H:i:s', $firstFlight['departure_datetime']),
                            'arrival_time' => date('Y-m-d H:i:s', $firstFlight['arrival_datetime']),
                        ]
                    ]
                ];
            }

            // Caso 2: Conexión con otro vuelo
            foreach ($events as $secondFlight) {
                if (
                    $firstFlight['arrival_city'] === $secondFlight['departure_city'] && 
                    $secondFlight['departure_datetime'] > $firstFlight['arrival_datetime'] && 
                    $secondFlight['departure_datetime'] - $firstFlight['arrival_datetime'] <= 4 * 3600 && //Conexion <= 4 horas
                    $secondFlight['arrival_city'] === $to //Destino final coincide
                ) {
                    $totalDuration = $secondFlight['arrival_datetime'] - $firstFlight['departure_datetime'];
                    if ($totalDuration <= 24 * 3600) { //Duracion total <= 24 horas
                        $journeys[] = [
                            'connections' => 2,
                            'path' => [
                                [
                                    'flight_number' => $firstFlight['flight_number'],
                                    'from' => $firstFlight['departure_city'],
                                    'to' => $firstFlight['arrival_city'],
                                    'departure_time' => date('Y-m-d H:i:s', $firstFlight['departure_datetime']),
                                    'arrival_time' => date('Y-m-d H:i:s', $firstFlight['arrival_datetime']),
                                ],
                                [
                                    'flight_number' => $secondFlight['flight_number'],
                                    'from' => $secondFlight['departure_city'],
                                    'to' => $secondFlight['arrival_city'],
                                    'departure_time' => date('Y-m-d H:i:s', $secondFlight['departure_datetime']),
                                    'arrival_time' => date('Y-m-d H:i:s', $secondFlight['arrival_datetime']),
                                ]
                            ]
                        ];
                    }
                }
            }
        }
    }


    return $journeys;
}



}
