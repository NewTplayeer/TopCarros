
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container"> 
        <a class="navbar-brand" href="index.php">TopCarros</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['usuario_nome'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</span>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Registro</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">DashBoard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Carrinho.php">Carrinho</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="historico_compras.php">Histórico de Compras</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>