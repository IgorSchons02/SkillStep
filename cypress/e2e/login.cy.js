describe('Funcionalidade de Login - SkillStep', () => {
  
  beforeEach(() => {
    // Certifique-se que a baseUrl está como http://localhost:suaporta no cypress.config.js
    cy.visit('/login'); 
  });

  it('Deve exibir a página de login corretamente', () => {
    cy.get('h2').should('contain', 'SkillStep');
    
    // Ajustado de 'have.text' para 'contain.text' para ignorar espaços e quebras de linha do Blade
    cy.get('.btn-login').should('contain.text', 'Entrar no Sistema');
  });

  it('Deve logar com sucesso com credenciais válidas', () => {
    cy.get('#email').type('admin@cigam.com.br');
    cy.get('#senha').type('123');
    
    cy.get('.btn-login').click();

    // Validação: Após o clique, a URL deve mudar. 
    // O 'should('not.include', '/login')' é bom, mas verificar a rota de destino é ainda mais seguro
    cy.url().should('not.include', '/login');
    
    // Dica: Se você tiver uma sidebar ou algo que carregue após o login, valide aqui:
    // cy.get('.sidebar').should('be.visible');
  });

  it('Deve exibir mensagem de erro ao falhar na autenticação', () => {
    cy.get('#email').type('invalido@teste.com');
    cy.get('#senha').type('12345678');
    cy.get('.btn-login').click();

    // Validação da mensagem de erro que vem do session('error') do Laravel
    cy.get('.alert-danger')
      .should('be.visible')
      .and('contain.text', 'E-mail ou senha inválidos');
  });

  it('Deve validar campos obrigatórios (HTML5)', () => {
    // Verifica se os inputs possuem o atributo 'required' conforme definido no seu Blade
    cy.get('#email').should('have.attr', 'required');
    cy.get('#senha').should('have.attr', 'required');
  });
});