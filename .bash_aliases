container=app-lambda

alias dkdown="docker-compose down"
alias dkup="docker-compose up -d --remove-orphans"
alias dkrest="dkdown && dkup"
alias dksh="docker-compose exec $container sh"
alias dkc="docker-compose exec $container composer"
alias dkart="docker-compose exec $container php artisan"
alias dklogs="docker-compose logs"
alias dklog="docker-compose logs $container"

function dkrebuild()
{
    dkdown && for i in $(docker images | grep app- | awk '{print $1}'); do docker rmi $i; done && dkup
}
