const status = (options) => ({ key: 'status', label: 'Statut', type: 'select', required: true, options });

export const modules = {
    users: {
        title: 'Utilisateurs & rôles', singular: 'utilisateur', endpoint: '/users', search: 'nom, identifiant ou e-mail',
        columns: [['name', 'Nom'], ['login', 'Identifiant'], ['email', 'E-mail'], ['role.name', 'Rôle'], ['is_active', 'Actif']],
        fields: [
            { key: 'name', label: 'Nom complet', required: true }, { key: 'login', label: 'Identifiant', required: true },
            { key: 'email', label: 'E-mail', type: 'email', required: true },
            { key: 'role_id', label: 'Rôle', type: 'select', required: true, options: [] },
            { key: 'password', label: 'Mot de passe', type: 'password' },
            { key: 'password_confirmation', label: 'Confirmation', type: 'password' },
            { key: 'is_active', label: 'Compte actif', type: 'checkbox' },
        ],
        defaults: { role_id: '', is_active: true },
    },
    clients: {
        title: 'Clients', singular: 'client', endpoint: '/clients', search: 'nom, e-mail, téléphone ou société',
        columns: [['name', 'Nom'], ['company', 'Société'], ['email', 'E-mail'], ['phone', 'Téléphone'], ['status', 'Statut']],
        fields: [
            { key: 'name', label: 'Nom', required: true }, { key: 'company', label: 'Société' },
            { key: 'email', label: 'E-mail', type: 'email' }, { key: 'phone', label: 'Téléphone' },
            { key: 'city', label: 'Ville' }, status([{ value: 'active', label: 'Actif' }, { value: 'inactive', label: 'Inactif' }]),
            { key: 'notes', label: 'Notes', type: 'textarea', wide: true },
        ],
        defaults: { status: 'active' },
    },
    leads: {
        title: 'Prospects', singular: 'prospect', endpoint: '/leads', search: 'nom, société ou source',
        columns: [['name', 'Nom'], ['company', 'Société'], ['source', 'Source'], ['value', 'Valeur (DT)'], ['status', 'Étape']],
        fields: [
            { key: 'name', label: 'Nom', required: true }, { key: 'company', label: 'Société' },
            { key: 'email', label: 'E-mail', type: 'email' }, { key: 'phone', label: 'Téléphone' },
            { key: 'source', label: 'Source' }, { key: 'value', label: 'Valeur estimée', type: 'number' },
            status([{ value: 'new', label: 'Nouveau' }, { value: 'contacted', label: 'Contacté' }, { value: 'qualified', label: 'Qualifié' }, { value: 'converted', label: 'Converti' }, { value: 'lost', label: 'Perdu' }]),
            { key: 'notes', label: 'Notes', type: 'textarea', wide: true },
        ],
        defaults: { status: 'new', value: 0 },
    },
    quotes: {
        title: 'Devis', singular: 'devis', endpoint: '/quotes', search: 'référence',
        columns: [['reference', 'Référence'], ['client.name', 'Client'], ['total', 'Total (DT)'], ['valid_until', 'Validité'], ['status', 'Statut']],
        fields: [
            { key: 'reference', label: 'Référence', required: true }, { key: 'client_id', label: 'ID client', type: 'number', required: true },
            { key: 'subtotal', label: 'Sous-total', type: 'number', required: true }, { key: 'tax', label: 'Taxes', type: 'number' },
            { key: 'total', label: 'Total', type: 'number', required: true }, { key: 'valid_until', label: 'Valide jusqu’au', type: 'date' },
            status([{ value: 'draft', label: 'Brouillon' }, { value: 'sent', label: 'Envoyé' }, { value: 'accepted', label: 'Accepté' }, { value: 'rejected', label: 'Refusé' }]),
            { key: 'notes', label: 'Notes', type: 'textarea', wide: true },
        ],
        defaults: { status: 'draft', subtotal: 0, tax: 0, total: 0 },
    },
    tasks: {
        title: 'Tâches & agenda', singular: 'tâche', endpoint: '/tasks', search: 'titre ou description',
        columns: [['title', 'Titre'], ['priority', 'Priorité'], ['due_at', 'Échéance'], ['assignee.name', 'Assignée à'], ['status', 'Statut']],
        fields: [
            { key: 'title', label: 'Titre', required: true },
            status([{ value: 'todo', label: 'À faire' }, { value: 'in_progress', label: 'En cours' }, { value: 'done', label: 'Terminée' }, { value: 'cancelled', label: 'Annulée' }]),
            { key: 'priority', label: 'Priorité', type: 'select', required: true, options: [{ value: 'low', label: 'Basse' }, { value: 'normal', label: 'Normale' }, { value: 'high', label: 'Haute' }, { value: 'urgent', label: 'Urgente' }] },
            { key: 'due_at', label: 'Échéance', type: 'datetime-local' },
            { key: 'description', label: 'Description', type: 'textarea', wide: true },
        ],
        defaults: { status: 'todo', priority: 'normal' },
    },
    'blog-posts': {
        title: 'Articles du blog', singular: 'article', endpoint: '/blog-posts', search: 'titre ou contenu',
        columns: [['title', 'Titre'], ['slug', 'URL'], ['author.name', 'Auteur'], ['published_at', 'Publication'], ['status', 'Statut']],
        fields: [
            { key: 'title', label: 'Titre', required: true }, { key: 'slug', label: 'Slug', required: true },
            { key: 'excerpt', label: 'Extrait', type: 'textarea', wide: true }, { key: 'content', label: 'Contenu', type: 'textarea', wide: true },
            status([{ value: 'draft', label: 'Brouillon' }, { value: 'published', label: 'Publié' }]),
            { key: 'published_at', label: 'Date de publication', type: 'datetime-local' },
        ],
        defaults: { status: 'draft' },
    },
    solutions: {
        title: 'Solutions métier', singular: 'solution', endpoint: '/solutions', search: 'titre ou description',
        columns: [['title', 'Titre'], ['slug', 'URL'], ['featured', 'Mise en avant'], ['sort_order', 'Ordre'], ['status', 'Statut']],
        fields: [
            { key: 'title', label: 'Titre', required: true }, { key: 'slug', label: 'Slug', required: true },
            { key: 'short_description', label: 'Description courte', type: 'textarea', wide: true },
            { key: 'description', label: 'Description complète', type: 'textarea', wide: true },
            status([{ value: 'draft', label: 'Brouillon' }, { value: 'published', label: 'Publié' }]),
            { key: 'featured', label: 'Mise en avant', type: 'checkbox' }, { key: 'sort_order', label: 'Ordre', type: 'number' },
        ],
        defaults: { status: 'draft', featured: false, sort_order: 0 },
    },
};
