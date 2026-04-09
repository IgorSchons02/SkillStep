describe('Gestão de Usuários - SkillStep', () => {
  
  beforeEach(() => {
    // 1. Faz o login usando seu comando customizado
    cy.visit('/login');
    cy.loginSkillStep('admin@cigam.com.br', '123');
    
    // 2. Navega para a página de usuários
    cy.visit('/admin/usuarios'); 
  });

  it('Deve cadastrar um novo usuário com sucesso via modal', () => {
    // Abrir a modal
    cy.contains('button', 'Novo Usuário').click();

    // Verificar se a modal está visível antes de interagir
    cy.get('#modalNovoUsuario').should('be.visible');

    // Preencher os campos
    cy.get('#modalNovoUsuario input[name="nome"]').type('teste');
    
    cy.get('#modalNovoUsuario input[name="cpf"]').type('12345678901'); 
    
    cy.get('#modalNovoUsuario select[name="tipo_usuario"]').select('Administrador');
    cy.get('#modalNovoUsuario input[name="email"]').type('igor.teste@cigam.com.br');
    cy.get('#modalNovoUsuario input[name="senha"]').type('senha123');

    // Clicar em salvar
    cy.get('#modalNovoUsuario button').contains('Salvar Usuário').click();

    // Validação: Verifique se o usuário aparece na tabela ou se a modal fechou
    cy.get('#modalNovoUsuario').should('not.be.visible');
    cy.contains('table', 'teste').should('be.visible');
  });

  it('Deve filtrar usuários pela busca em tempo real', () => {
    // Testando sua lógica de "keyup" com timeout de 500ms
    cy.get('#searchInput').type('teste');
    
    // Esperamos um pouco mais que os 500ms do seu script para o reload acontecer
    cy.wait(1000); 
    
    cy.get('table').should('contain', 'teste');
  });
});