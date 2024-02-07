<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
// Função para calcular a porcentagem de uma letra em uma string
const calcularPorcentagem = (letra, string) => ((string.match(new RegExp(letra, 'g')) || []).length / string.length) / 25 * 100;

// Função para determinar as duas letras com as maiores porcentagens
const determinarDuasMaioresPorcentagens = (contadores) => {
  // Criar um array de objetos com as letras e suas porcentagens
  const porcentagens = Object.keys(contadores).map(letra => ({ letra, porcentagem: contadores[letra] }));

	console.log(porcentagens)
	
  // Ordenar o array por porcentagem em ordem decrescente
  porcentagens.sort((a, b) => b.porcentagem - a.porcentagem);

	// Criar um gráfico de barras
  const ctx = document.getElementById('graficoPorcentagens').getContext('2d');
  const myChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: porcentagens.map(item => `${obterTitulo(item.letra)} ${parseFloat(item.porcentagem.toFixed(2))}%`), 
      datasets: [{
        label: 'Porcentagem',
        data: porcentagens.map(item => item.porcentagem),
        backgroundColor: ['#bdc3c7', '#c0392b', '#f1c40f', '#3498db'], // Cores para cada letra
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          max: 100
        }
      }
    }
  });

  // Exibir as descrições das duas letras com as maiores porcentagens
  const letra1 = porcentagens[0].letra;
  const letra2 = porcentagens[1].letra;

  let divResume = document.querySelector('.resume-main .col-md-8');

  let resultadoDiv = document.createElement('div');

  resultadoDiv.classList.add('resultado');
  let resultadoA = document.createElement('h2');
  let resultadoB = document.createElement('h2');
  let descricaoA = document.createElement('p');
  let descricaoB = document.createElement('p');

  resultadoA.innerHTML = `${obterTitulo(letra1)}`
  resultadoB.innerHTML = `${obterTitulo(letra2)}`
  descricaoA.innerHTML = `${obterDescricao(letra1)}`
  descricaoB.innerHTML = `${obterDescricao(letra2)}`

  divResume.prepend(resultadoDiv)
  resultadoDiv.appendChild(resultadoA)
  resultadoDiv.appendChild(descricaoA)
  resultadoDiv.appendChild(resultadoB)
  resultadoDiv.appendChild(descricaoB)
};

// Função para obter o título com base na letra
const obterTitulo = (letra) => {
  const titulosPorLetra = {
    A: 'Tubarão',
    C: 'Gato',
    I: 'Águia',
    O: 'Lobo',
  };

  return titulosPorLetra[letra] || 'Título não encontrado.';
};

// Função para obter a descrição com base na letra
const obterDescricao = (letra) => {
  const descricaoPorLetra = {
    A: `
      <p><strong>Principal:</strong> Fazer rápido (Atitude/ação)</p>
      <p><strong>Comportamento:</strong> Senso de urgência, iniciativa, prático, impulsivo, vencer desafios, aqui e agora, auto suficiente, não delegar.</p>
      <p><strong>Pontos fortes:</strong> Ação, Fazer que ocorra, parar com a burocracia, motivação.</p>
      <p><strong>Pontos de melhorias:</strong> Socialmente um desastre, faz da forma mais fácil, relacionamento complicado. Precisa melhorar a paciência, atenção às pessoas, humildade, consideração, trabalhar coletivamente, Ouvir mais.</p>
      <p><strong>Motivações:</strong> Liberdade para agir individualmente, controle das próprias atividades, resolver os problemas do seu jeito, competição, variedade de atividades, não ter que repetir tarefas.</p>
      <p><strong>Valores:</strong> Resultado</p>`,
    C: `
      <p><strong>Principal:</strong> Fazer junto (Comunicação)</p>
      <p><strong>Comportamento:</strong> Sensível, relacionamentos, time, tradicional, contribuição, busca harmonia, delega autoridade.</p>
      <p><strong>Pontos fortes:</strong> Comunicação, mantém a harmonia, desenvolve e mantém a cultura, comunicação aberta.</p>
      <p><strong>Pontos de melhorias:</strong> Esconder conflitos, felicidade acima dos resultados, manipulação através de sentimentos. Abordagem mais direta, controle de tempo, controle emocional, mais foco, prazos realistas, trabalhar mais a razão.</p>
      <p><strong>Motivações:</strong> Segurança, aceitação social, construir o consenso, reconhecimento da equipe, supervisão compreensiva, ambiente harmônico, trabalho em grupo.</p>
      <p><strong>Valores:</strong> Felicidade e igualdade (pensa nos outros)</p>`,
    I: `
      <p><strong>Principal:</strong> Fazer diferente (Idealização)</p>
      <p><strong>Comportamento:</strong> Criativo, intuitivo, foco no futuro, distraído, curioso, informal e flexível.</p>
      <p><strong>Pontos fortes:</strong> Idealização, Provoca mudanças, antecipa o futuro, criatividade.</p>
      <p><strong>Pontos de melhorias:</strong> Falta de atenção no presente, impaciência e rebeldia, defender o novo pelo novo, trabalho em equipe, verbalização.</p>
      <p><strong>Motivações:</strong> Liberdade de expressão, Ausência de controle rígido, oportunidade para delegar.</p>
      <p><strong>Valores:</strong> Criatividade e liberdade (inspirar ideias)</p>`,
    O: `
      <p><strong>Principal:</strong> Fazer certo (Organização)</p>
      <p><strong>Comportamento:</strong> Detalhista, organizado, estrategista, busca do conhecimento, pontual, conservador, previsível.</p>
      <p><strong>Pontos fortes:</strong> Organização, passado presente e futuro, consistência, conformidade e qualidade, lealdade e segurança, regras e responsabilidades.</p>
      <p><strong>Pontos de melhorias:</strong> Dificuldade de se adaptar a mudanças, pode impedir o progresso, detalhista, estruturado e demasiadamente sistematizado. Melhorar o entusiasmo, flexibilidade, aceitação de outros estilos comportamentais, método de atalho.</p>
      <p><strong>Motivações:</strong> Certeza, compreensão exata das regras, conhecimento específico, ausência de riscos e erros, vero produto acabado (começo, meio e fim).</p>
      <p><strong>Valores:</strong> Ordem e controle</p>`,
  };

  return descricaoPorLetra[letra] || 'Descrição não encontrada.';
};

// Função principal para processar os valores
const processarValores = () => {
  // Inicializar um objeto para armazenar contadores de letras
  const contadores = { A: 0, C: 0, I: 0, O: 0 };

  // Loop de 1 a 25 para processar cada elemento
  for (let i = 1; i <= 25; i++) {
    // Obter o valor dentro do elemento jmfe-custom-pergunta_
    const valor = document.getElementById(`jmfe-custom-pergunta_${i}`).innerText.trim().toUpperCase();

    // Atualizar os contadores com base no valor
    Object.keys(contadores).forEach(letra => contadores[letra] += calcularPorcentagem(letra, valor));
  }

  // Chamar a função para determinar as duas letras com as maiores porcentagens
  determinarDuasMaioresPorcentagens(contadores);
};

// Chamar a função principal quando a página estiver totalmente carregada
window.addEventListener('load', processarValores);




var elementos = document.querySelectorAll('[class^="fieldset-pergunta_"]');
var indiceAtual = 0;

function mostrarElemento(indice) {
    elementos[indiceAtual].classList.remove('ativo');
    elementos[indice].classList.add('ativo');

    // Atualiza o índice atual
    indiceAtual = indice;
}

elementos.forEach((elemento, indice) => {
    let createButtonNext = document.createElement('p');
    createButtonNext.innerText = 'Próximo';
	createButtonNext.classList.add('proxbtn');

    // Adiciona um evento de clique ao botão "Próximo"
    createButtonNext.addEventListener('click', function() {
        // Mostra o próximo elemento ao clicar no botão
        mostrarElemento(indice + 1);

        // Você pode adicionar lógica adicional aqui se quiser
    });

    // Adiciona o botão "Próximo" ao elemento
    elemento.appendChild(createButtonNext);

    // Adiciona um botão "Anterior" para todos os elementos, exceto o primeiro
    if (indice > 0) {
        let createButtonPrev = document.createElement('p');
        createButtonPrev.innerText = 'Anterior';
		createButtonPrev.classList.add('proxbtn');
		

        // Adiciona um evento de clique ao botão "Anterior"
        createButtonPrev.addEventListener('click', function() {
            // Mostra o elemento anterior ao clicar no botão "Anterior"
            mostrarElemento(indice - 1);

            // Você pode adicionar lógica adicional aqui se quiser
        });

        // Adiciona o botão "Anterior" ao elemento
        elemento.appendChild(createButtonPrev);
    }
});

// Inicialmente, define o primeiro elemento como ativo
mostrarElemento(0);</script>
<!-- end Simple Custom CSS and JS -->
