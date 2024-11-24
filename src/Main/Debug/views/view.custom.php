<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/assets/bootstrap.min.css">
    <title>Error Page</title>
</head>

<body>
    <h1>Message: {{$message}}</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stack as $class)
            <tr>
                <td>
                    <p>{{$class}}</p>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>