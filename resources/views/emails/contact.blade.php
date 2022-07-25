<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        h3{
            color : green;
        }
    </style>
</head>
<body>
    <h3>{{ $details['title'] }}</h3>
    <br>
    <p>Comentario:</p>
    <p><strong>{{ $details['body'] }}</strong></p>
</body>
</html>
