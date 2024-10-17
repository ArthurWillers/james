function tocarSom(som) {
  som = document.getElementById(som);
  som.play();
}

document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && event.altKey && event.key === 'u') {
        tocarSom("somSecret")
    }
});