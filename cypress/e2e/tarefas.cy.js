describe("Gestão de Tarefas - SkillStep", () => {
    beforeEach(() => {
        // Login e navegação
        cy.visit("/login");
        cy.loginSkillStep("admin@cigam.com.br", "123");

        // Ajuste a rota se o seu sistema não usar o prefixo /admin
        cy.visit("/admin/tarefas");
    });

    it("Deve criar uma nova tarefa com sucesso", () => {
        cy.contains("button", "Nova Tarefa").click();

        // Aguarda a modal abrir completamente
        cy.get("#modalNovaTarefa")
            .should("be.visible")
            .should("have.class", "show");

        cy.get("#modalNovaTarefa").within(() => {
            // Usando invoke para garantir a injeção do texto
            cy.get('input[name="titulo"]')
                .invoke("val", "Configurar Servidor VPN")
                .trigger("input")
                .trigger("change");

            // Para selects, o Cypress tem o comando nativo .select()
            // Seleciona o index 1 (a primeira categoria real após o "Selecione...")
            cy.get('select[name="categoria_id"]').select(1);

            cy.get('input[name="tempo_estimado"]')
                .invoke("val", "2.5")
                .trigger("input")
                .trigger("change");

            cy.get('textarea[name="descricao"]')
                .invoke(
                    "val",
                    "Passo a passo para instalar o cliente VPN e conectar na rede corporativa.",
                )
                .trigger("input")
                .trigger("change");

            // Testa o switch de status (se quiser testar desmarcando, use .uncheck())
            cy.get("#statusNovo").should("be.checked");

            cy.get('button[type="submit"]').click();
        });

        // Valida se a tarefa apareceu na tabela
        cy.get("table").should("contain", "Configurar Servidor VPN");
    });

    it("Deve visualizar os detalhes de uma tarefa", () => {
        // Clica no botão de visualizar (olho) do primeiro registro da tabela
        cy.get('[data-bs-target="#modalVisTarefa"]').first().click();

        cy.get("#modalVisTarefa")
            .should("be.visible")
            .should("have.class", "show");

        // Valida se os campos dinâmicos da modal foram preenchidos pelo seu JavaScript
        cy.get("#vis_titulo").should("not.be.empty");
        cy.get("#vis_categoria").should("not.be.empty");
    });

    it("Deve editar uma tarefa existente", () => {
        // Clica no botão de edição (lápis) do primeiro registro
        cy.get('[data-bs-target="#modalEditarTarefa"]').first().click();

        // Usamos os IDs específicos que você colocou no form de edição
        cy.get("#edit_titulo")
            .invoke("val", "Tarefa Atualizada pelo Cypress")
            .trigger("input")
            .trigger("change");

        // Testa o switch de inativar a tarefa
        cy.get("#edit_status").uncheck({ force: true });
        // Valida se o seu JS atualizou a label para "Inativo"
        cy.get("#labelStatusEdit").should("have.text", "Inativo");

        cy.get('#modalEditarTarefa button[type="submit"]').click();

        // Valida se a alteração refletiu na tabela
        cy.get("table").should("contain", "Tarefa Atualizada pelo Cypress");
        cy.get("table").find(".badge").should("contain", "Inativo");
    });

    it("Deve testar todos os filtros em conjunto", () => {
        // 1. Pesquisa por texto
        cy.get("#searchInput")
            .invoke("val", "Cypress")
            .trigger("input")
            .trigger("keyup");

        cy.wait(600); // Aguarda o debounce de 500ms + resposta

        // 2. Filtro de Categoria
        cy.get("#filtroCategoria").select(1);
        cy.wait(600); // Como é disparado no 'change', ele envia o form

        // 3. Filtro de Status
        // O value "1" é Apenas Ativos, "0" é Apenas Inativos
        cy.get("#filtroStatus").select("1");
        cy.wait(600);

        // Validação básica da tabela
        cy.get(".table-responsive").should("exist");
    });

    it("Deve cancelar a exclusão de uma tarefa no Alert", () => {
        // Clica no botão excluir (lixeira) do primeiro registro
        cy.get(".btn-delete").first().click();

        // Valida a presença do alerta
        cy.get(".swal2-modal").should("be.visible");
        cy.get(".swal2-title").should("contain", "Excluir Tarefa?");

        // CORREÇÃO: Restringimos a busca do botão APENAS dentro do modal do SweetAlert
        cy.get(".swal2-modal").contains("button", "Cancelar").click();

        // Garante que o alerta fechou e a tabela continua lá
        cy.get(".swal2-modal").should("not.exist");
        cy.get("table").should("be.visible");
    });
});
