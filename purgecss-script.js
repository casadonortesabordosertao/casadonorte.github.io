// Importa os módulos necessários
const { PurgeCSS } = require('purgecss');
const fs = require('fs');
const path = require('path');

// Função assíncrona para executar o PurgeCSS e salvar os arquivos
async function runPurge() {
  // Executa o PurgeCSS
  const results = await new PurgeCSS().purge({
    content: ['./*.php', './footer/*.php'],  // Caminhos para seus arquivos PHP
    css: ['./assets/css/*.css']              // Caminhos para seus arquivos CSS
  });

  // Para cada arquivo purgado, escreva o conteúdo no arquivo final
  for (const file of results) {
    // Pega o nome do arquivo de origem
    const nome = path.basename(file.file);

    // Salva o CSS purgado no diretório de saída (purged-css)
    fs.writeFileSync(`./purged-css/${nome}`, file.css);
  }

  console.log('CSS purgado com sucesso!');
}

// Executa a função
runPurge().catch(err => console.error('Erro ao purgar o CSS:', err));
