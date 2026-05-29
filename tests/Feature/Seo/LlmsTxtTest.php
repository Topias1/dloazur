<?php

it('/llms.txt returns 200 with text content-type', function () {
    $response = $this->get('/llms.txt');

    $response->assertStatus(200);
    expect($response->headers->get('Content-Type'))->toStartWith('text/');
});

it('/llms.txt body contains Dlo Azur Piscines', function () {
    $response = $this->get('/llms.txt');

    $response->assertStatus(200);
    $response->assertSee('Dlo Azur Piscines', false);
});

it('/llms.txt does not expose admin or auth URLs', function () {
    $response = $this->get('/llms.txt');

    $response->assertStatus(200);
    $response->assertDontSee('/admin', false);
    $response->assertDontSee('/login', false);
});
