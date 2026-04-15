## Configuration ChatGPT pour le Module d'Onboarding

### 1. Obtenir votre clé API OpenAI

1. Allez sur https://platform.openai.com/account/api-keys
2. Créez une nouvelle clé API secrète
3. Copiez la clé (vous ne pourrez plus la voir après)

### 2. Configurer la clé localement

Option A: Dans `.env.local` (recommandé - ne pas commiter)

```bash
# .env.local
OPENAI_API_KEY=sk-proj-VOTRE_CLE_ICI
```

Option B: Variable d'environnement système

```bash
export OPENAI_API_KEY=sk-proj-VOTRE_CLE_ICI
```

### 3. Vérifier la configuration

```bash
php bin/console debug:container App\\Service\\AiChatServiceInterface
```

Vous devez voir `App\Service\ChatGptService` enregistré.

### 4. Tester le chat

1. Lancez l'app: `php -S 127.0.0.1:8000 -t public`
2. Accédez à: `http://localhost:8000/onboarding`
3. Créez un nouvel onboarding et testez le chat

### 5. Modèles disponibles

Le service utilise par défaut `gpt-4o-mini` (plus rapide et moins cher).

Pour changer le modèle:

```php
$chatGptService->setModel('gpt-4');  // Plus puissant
$chatGptService->setModel('gpt-4-turbo'); // Plus nouveau
```

### 6. Coûts/Limites

- **gpt-4o-mini**: ~$0.15 / 1M tokens (recommandé)
- **gpt-4**: ~$0.03 / 1K tokens
- Vérifiez vos limites sur https://platform.openai.com/account/billing/limits

### 7. Fallback au Mock

Si ChatGPT n'est pas configuré, changez dans `config/services.yaml`:

```yaml
App\Service\AiChatServiceInterface:
    class: App\Service\MockAiChatService
```

### 8. Troubleshooting

**"OPENAI_API_KEY is not set"**

- Vérifiez que vous avez défini la variable dans .env.local ou l'environnement système
- Relancez le serveur après modification

**"401 Unauthorized"**

- Clé API expirée ou invalide
- Générez une nouvelle clé

**"Rate limit exceeded"**

- Vous avez dépassé votre quota
- Vérifiez votre plan sur https://platform.openai.com/account/billing/overview

### 9. Production

Pour la production, utilisez Symfony Secrets:

```bash
php bin/console secrets:set OPENAI_API_KEY
```

Plus info: https://symfony.com/doc/current/configuration/secrets.html
