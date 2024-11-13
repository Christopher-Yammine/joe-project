<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stream;
use Illuminate\Support\Str;

class StreamController extends Controller
{
    public function getAllStreams()
    {
        $streams = Stream::all();

        $groupedStreams = $streams->groupBy(function ($stream) {
            return Str::before($stream->name, ' ');
        });
    
        $response = $groupedStreams->map(function ($group, $key) {
            return [
                'label' => $key,
                'value' => strtolower($key),
                'options' => $group->map(function ($stream) {
                    return [
                        'label' => $stream->name,
                        'value' => $stream->id,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();
    
        return response()->json($response);
    }
}
