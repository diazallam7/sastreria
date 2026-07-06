<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Inicio de sesión del sistema Sastrería Medina" />
    <meta name="author" content="Allam Diaz" />
    <title>Sastrería Medina - Inicio de Sesión</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    
    <link href="{{ asset('css/template.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    
    <style>
        body {
            background-color: #0a0a0a;
            background-image: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            font-family: 'Montserrat', sans-serif;
            height: 100vh;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            background-color: #fff;
            overflow: hidden;
        }

        .card-header {
            background-color: #fff;
            border-bottom: none;
            padding-top: 2rem;
            padding-bottom: 0;
        }

        .brand-logo {
            max-width: 120px;
            display: block;
            margin: 0 auto 1rem;
            border-radius: 50%; /* Optional: based on logo shape */
        }

        .card-header h3 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #000;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 1.5rem;
        }

        .form-floating label {
            color: #666;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.25rem rgba(0, 0, 0, 0.1);
        }

        .btn-login {
            background-color: #000;
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            padding: 0.8rem;
            border-radius: 5px;
            border: 1px solid #000;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-login:hover {
            background-color: #333;
            border-color: #333;
            color: #fff;
            transform: translateY(-2px);
        }

        .footer-text {
            color: #888;
            font-size: 0.85rem;
        }

        .footer-text a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-text a:hover {
            color: #fff;
        }

        #layoutAuthentication {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        #layoutAuthentication_content {
            flex-grow: 1;
            display: flex;
            align-items: center;
        }

        .alert-secondary {
            background-color: #f8f9fa;
            border-color: #ddd;
            color: #333;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main class="w-100">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6">
                            <div class="card mt-5">
                                <div class="card-header text-center">
                                    <img src="{{ asset('medina.jpg') }}" alt="Sastrería Medina" class="brand-logo shadow-sm">
                                    <h3 class="my-3">Iniciar Sesión</h3>
                                </div>
                                <div class="card-body px-4 pb-4">
                                    @if ($errors->any())
                                        @foreach ($errors->all() as $item)
                                            <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                                                <i class="fas fa-exclamation-circle me-2"></i> {{ $item }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endforeach
                                    @endif

                                    <form action="/login" method="POST">
                                        @csrf
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="email" id="inputEmail" type="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus />
                                            <label for="inputEmail"><i class="fas fa-envelope me-2"></i>Correo Electrónico</label>
                                        </div>
                                        <div class="form-floating mb-4">
                                            <input class="form-control" name="password" id="inputPassword" type="password" placeholder="Password" required />
                                            <label for="inputPassword"><i class="fas fa-lock me-2"></i>Contraseña</label>
                                        </div>
                                        
                                        <div class="mt-4 mb-0">
                                            <button type="submit" class="btn btn-login">
                                                Ingresar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            <footer class="py-4 mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-center small">
                        <div class="footer-text text-center">
                            Copyright &copy; Sastrería Medina 2026 | Desarrollado por 
                            <a href="https://www.instagram.com/allamdiaz7/" target="_blank">Allam Diaz</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
</body>

</html>
