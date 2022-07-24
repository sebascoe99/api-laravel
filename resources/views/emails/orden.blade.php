<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        h1{
            color : blue;
        }
    </style>
</head>
<body>
    <h1>{{ $details['title'] }}</h1>
    <p>{{ $details['body'] }}</p>
    <p>Los detalles de su pedido se pueden encontrar en <a href="ferreteriaeldescanso.com">ferreteriaeldescanso.com</a> en la sección pedidos.</p>
    <br>
    <p>Gracias por su confianza.</p>
</body>
</html>
