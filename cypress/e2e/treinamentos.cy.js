describe("Gestão de Treinamentos - SkillStep", () => {
    beforeEach(() => {
        // Autenticação e navegação
        cy.visit("/login");
        cy.loginSkillStep("admin@cigam.com.br", "123");

        // Ajuste a rota se necessário
        cy.visit("/admin/treinamentos");
    });

    it("Deve criar um novo treinamento e adicionar tarefas à jornada", () => {
        cy.contains("button", "Novo Treinamento").click();

        // Aguarda a modal de criação abrir totalmente
        cy.get("#modalTreinamento")
            .should("be.visible")
            .should("have.class", "show");

        // Preenche os dados básicos com invoke para estabilidade
        cy.get("#nome")
            .invoke("val", "Trilha E2E com Cypress")
            .trigger("input")
            .trigger("change");

        cy.get("#descricao")
            .invoke(
                "val",
                "Treinamento focado em testes automatizados e qualidade de software.",
            )
            .trigger("input")
            .trigger("change");

        // Interação com o Dual List Box (Adicionar Tarefa)
        // Pega o primeiro botão disponível na coluna da esquerda e clica
        cy.get("#listaDisponiveis button").first().click();

        // Valida se a tarefa foi para a coluna da direita (listaSelecionadas)
        cy.get("#listaSelecionadas .list-group-item").should(
            "have.length.at.least",
            1,
        );

        // Valida se o contador de tempo e de tarefas atualizou via JavaScript
        cy.get("#countSelecionadas").should("not.have.text", "0");
        cy.get("#tempoTotalModal").should("not.have.text", "0h");

        // Clica em salvar
        cy.contains("button", "Salvar Treinamento").click();

        // Verifica se o card foi gerado no grid
        cy.get(".card-treinamento").should("contain", "Trilha E2E com Cypress");
    });

    it("Deve visualizar os detalhes do treinamento", () => {
        // Clica no botão visualizar (ícone de olho) do primeiro card do grid
        cy.get(".card-treinamento")
            .first()
            .find(".bi-eye")
            .closest("button")
            .click();

        cy.get("#modalVisualizarTreinamento")
            .should("be.visible")
            .should("have.class", "show");

        // Valida o preenchimento dinâmico do JS na modal
        cy.get("#viewNome").should("not.be.empty");
        cy.get("#viewTotalTarefas").should("not.have.text", "0");
        cy.get("#viewListaTarefas").find(".list-group-item").should("exist");

        // Fecha a modal
        cy.get("#modalVisualizarTreinamento")
            .contains("button", "Fechar")
            .click();
    });

    it("Deve editar um treinamento e testar o switch de status", () => {
        // Clica no botão editar (ícone de lápis) do primeiro card
        cy.get(".card-treinamento")
            .first()
            .find(".bi-pencil")
            .closest("button")
            .click();

        cy.get("#modalTreinamento")
            .should("be.visible")
            .should("have.class", "show");

        cy.get("#nome")
            .invoke("val", "Treinamento Atualizado Módulo 1")
            .trigger("input")
            .trigger("change");

        // Desmarca o switch de ativo
        cy.get("#ativo").uncheck({ force: true });
        cy.get("#labelAtivo").should("have.text", "Treinamento Inativo");

        cy.contains("button", "Salvar Treinamento").click();

        // Valida se a alteração refletiu na listagem (nome e badge Inativo)
        cy.get(".card-treinamento")
            .first()
            .should("contain", "Treinamento Atualizado Módulo 1");
        cy.get(".card-treinamento")
            .first()
            .find(".badge")
            .should("contain", "Inativo");
    });

    it("Deve impedir de salvar um treinamento sem tarefas", () => {
        cy.contains("button", "Novo Treinamento").click();

        cy.get("#modalTreinamento").should("have.class", "show");

        cy.get("#nome")
            .invoke("val", "Treinamento Vazio")
            .trigger("input")
            .trigger("change");

        // Clica em salvar DIRETO, sem escolher nenhuma tarefa no Dual List Box
        cy.contains("button", "Salvar Treinamento").click();

        // Valida se a sua div de erro '#erroListaVazia' apareceu bloqueando o envio
        cy.get("#erroListaVazia").should("not.have.class", "d-none");
        cy.get("#erroListaVazia").should("be.visible");
    });

    it("Deve testar os filtros de pesquisa e status", () => {
        // Pesquisa via input
        cy.get("#searchInput")
            .invoke("val", "Módulo 1")
            .trigger("input")
            .trigger("keyup");

        cy.wait(600); // Tempo do debounce + carregamento

        // Filtro via select de status
        cy.get("#filtroStatus").select("0"); // Filtra apenas Inativos
        cy.wait(600);

        // Garante que o grid de resultados não "quebrou" na renderização
        cy.get(".row-cols-1").should("exist");
    });

    it("Deve cancelar a exclusão no SweetAlert2", () => {
        // Clica no botão lixeira do primeiro card
        cy.get(".btn-delete").first().click();

        // Restringe a busca apenas ao modal do alerta
        cy.get(".swal2-modal").should("be.visible");
        cy.get(".swal2-title").should("contain", "Excluir Treinamento?");

        // Clica em Cancelar
        cy.get(".swal2-modal").contains("button", "Cancelar").click();

        // Verifica se o modal fechou e a tela de fundo está limpa
        cy.get(".swal2-modal").should("not.exist");
        cy.get(".card-treinamento").should("be.visible");
    });
});
