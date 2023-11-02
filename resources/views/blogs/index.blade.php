<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <style>
        .custom-table {
            width: 100%;
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .custom-table th,
        .custom-table td {
            padding: 5px;
            text-align: left;
        }

        .custom-table th {
            background-color: #5c5e5c;
        }

        .custom-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .custom-table tbody tr:nth-child(even) {
            background-color: #e0e0e0;
        }

        .actions-column {
            width: 100px;
        }
    </style>


    <title>Blogs</title>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand h1" href={{ route('blogs.index') }}>Blogs</a>
            <div class="justify-end ">
                <div class="col ">
                    <a class="btn btn-sm btn-success" href={{ route('blogs.create') }}>Add Post</a>
                </div>
            </div>
    </nav>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="container mt-5">
        <table class="table table-bordered custom-table">
            <thead>
                <tr>
                    <th style="width: 5px">No</th>
                    <th>Title</th>
                    <th>Info</th>
                    <th class="actions-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if ($blogs->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center">
                            {{ $message }}
                        </td>
                    </tr>
                @else
                    @foreach ($blogs as $post)
                        <tr>
                            <td style="width: 5px">{{ $startNumber++ }}</td>
                            <td>{{ $post->title }}</td>
                            <td>{{ $post->info }}</td>
                            <td class="d-flex justify-content-end">
                                <a href="{{ route('blogs.edit', $post->id) }}"
                                    class="btn btn-primary btn-sm me-2">Edit</a>
                                <form action="{{ route('blogs.destroy', $post->id) }}" method="post" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $blogs->appends(request()->query())->links('custom.pagination') }}
        </div>
    </div>
</body>

</html>
