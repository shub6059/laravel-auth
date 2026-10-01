#!/usr/bin/env bash
set -Eeuo pipefail

project_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

usage() {
    printf 'Usage: %s [setup|down|logs|artisan ...]\n' "$0"
}

require_podman() {
    if ! command -v podman >/dev/null 2>&1; then
        printf 'Podman is required.\n' >&2
        exit 1
    fi
    if ! podman compose version >/dev/null 2>&1; then
        printf 'A Podman Compose provider is required for `podman compose`.\n' >&2
        exit 1
    fi
}

setup() {
    require_podman

    if [[ ! -f .env ]]; then
        cp .env.example .env
    fi

    podman run --rm \
        --volume "$project_dir:/workspace:z" \
        --workdir /workspace \
        docker.io/library/node:22-bookworm \
        sh -lc 'npm install && npm run build'

    podman compose up -d --build

    wait_for_app

    if ! grep -Eq '^APP_KEY=.+$' .env; then
        podman compose exec -T app php artisan key:generate
        podman compose restart app
        wait_for_app
    fi

    podman compose exec -T app php artisan migrate --force
    if database_has_users; then
        printf 'Existing users found; skipping seeders.\n'
    else
        podman compose exec -T app php artisan db:seed --force
    fi
    podman compose exec -T app php artisan storage:link

    app_port=8000
    if grep -Eq '^APP_PORT=[0-9]+' .env; then
        app_port="$(sed -n 's/^APP_PORT=//p' .env | head -n 1 | tr -d '\r"')"
    fi
    printf '\nLaravel Auth is ready at http://localhost:%s\n' "$app_port"
    printf 'Use `./scripts/podman-dev.sh down` to stop the containers without deleting data.\n'
}

wait_for_app() {
    printf 'Waiting for the Laravel app to start...\n'
    app_ready=false
    for ((attempt = 0; attempt < 90; attempt++)); do
        if podman compose exec -T app php -r '$socket = @fsockopen("127.0.0.1", 8000); exit($socket ? 0 : 1);' >/dev/null 2>&1; then
            app_ready=true
            break
        fi
        sleep 2
    done

    if [[ "$app_ready" != true ]]; then
        printf 'The Laravel app did not become ready. Recent app logs:\n' >&2
        podman compose logs --tail=50 app >&2
        exit 1
    fi
}

database_has_users() {
    podman compose exec -T app php -r '
        define("LARAVEL_START", microtime(true));
        require "vendor/autoload.php";
        $app = require "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        exit(App\Models\User::query()->exists() ? 0 : 1);
    ' >/dev/null 2>&1
}

command="${1:-setup}"
case "$command" in
    setup)
        setup
        ;;
    down)
        require_podman
        podman compose down
        ;;
    logs)
        require_podman
        podman compose logs -f
        ;;
    artisan)
        require_podman
        shift
        if [[ "$#" -eq 0 ]]; then
            usage >&2
            exit 2
        fi
        podman compose exec app php artisan "$@"
        ;;
    -h|--help|help)
        usage
        ;;
    *)
        usage >&2
        exit 2
        ;;
esac