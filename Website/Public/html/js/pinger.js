function ping()
{
    endpoint("/authentication/ping", "GET", null, (response) => {})
}

setInterval(() => { ping() }, 20000)