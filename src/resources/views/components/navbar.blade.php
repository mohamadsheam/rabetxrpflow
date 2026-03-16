<nav class="navbar navbar-light bg-light border-bottom px-4">

    <span class="navbar-brand">Dashboard</span>

    <div>
        <span class="me-3">{{ auth()->user()->name }}</span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-danger">
                Logout
            </button>
        </form>
    </div>

</nav>
