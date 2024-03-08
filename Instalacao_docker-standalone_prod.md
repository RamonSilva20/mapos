# Instalação Docker Produção


![MapOS](https://raw.githubusercontent.com/RamonSilva20/mapos/master/assets/img/logo.png)

### PRÉ-REQUISITOS

-  Docker, Docker Compose e Traefik em modo **standalone** [Guia aqui](https://github.com/JobasFernandes/instalar-traefik-standalone)

- Após ter o Docker e o Traefik instalado, baixe a ultima versão do **mapos** dentro da vps executando no terminal o comando abaixo
```shell
apt install zip -y && wget --quiet --show-progress -O $(pwd)/MapOS.zip $(curl -s https://api.github.com/repos/RamonSilva20/mapos/releases/latest | grep "zipball_url" | awk -F\" '{print $4}') && unzip -q $(pwd)/MapOS.zip -d $(pwd)/mapos-temp/ && rm $(pwd)/MapOS.zip && mv -i $(pwd)/mapos-temp/RamonSilva20-mapos-* $(pwd)/mapos/ && rm -rf $(pwd)/mapos-temp && cd $(pwd)/mapos
```
- Execute o comando abaixo para baixar e extrair os arquivos necessarios do docker para a raiz do mapos em sua vps
```shell
wget https://raw.githubusercontent.com/JobasFernandes/mapos/docker/docker-compose.yaml
```
- Gere a imagem do **MAPOS**
```shell
docker build -t mapos:latest .
```
- Edite o docker-compose.yaml e coloque os dados da sua instalação, labels e network do traefik

1. **`${MAPOS_URL}`**
2. **`${MYSQL_MAPOS_DATABASE}`**
3. **`${MYSQL_MAPOS_ROOT_PASSWORD}`**
4. **`${MYSQL_MAPOS_USER}`**
5. **`${MYSQL_MAPOS_PASSWORD}`**

- Suba a compose do MAPOS
```shell
docker compose up -d
```
> **IMPORTANTE:** O primeiro acesso deve ser feito usando protocolo http, Ex: **`http://mapos.seudominio.com.br`** após a configuração do banco de dados e dados de login clique em **IR PARA A PÁGINA DE LOGIN** que será redirecionado automaticamente para https usando certificado ssl auto-assinado.