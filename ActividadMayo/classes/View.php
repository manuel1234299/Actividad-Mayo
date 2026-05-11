<?php

class View
{
    public function renderHeader(string $title = 'Registro de usuario'): void
    {
        echo '<!DOCTYPE html>';
        echo '<html lang="es">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>' . htmlspecialchars($title) . '</title>';
        echo '<style>
            body { font-family: Arial, sans-serif; background: #f4f6fb; color: #222; margin: 0; padding: 0; }
            .container { max-width: 700px; margin: 0 auto; padding: 20px; }
            .card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
            h1 { margin-top: 0; }
            .field { margin-bottom: 18px; }
            label { display: block; margin-bottom: 8px; font-weight: bold; }
            input[type="text"], input[type="email"], input[type="password"], input[type="file"] { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; }
            .error { color: #c0392b; margin-top: 6px; }
            .button { background: #2c7be5; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; }
            .button:hover { background: #1a6ed8; }
            a { color: #2c7be5; text-decoration: none; }
            .note { font-size: 0.95rem; color: #555; }
        </style>';
        echo '</head>';
        echo '<body><div class="container">';
    }

    public function renderFooter(): void
    {
        echo '</div></body></html>';
    }

    public function renderRegisterForm(array $data = [], array $errors = []): void
    {
        $this->renderHeader();
        echo '<div class="card">';
        echo '<h1>Registro de usuario</h1>';
        echo '<form method="post" enctype="multipart/form-data" novalidate>';

        echo $this->renderField('name', 'Nombre completo', 'text', $data['name'] ?? '', $errors);
        echo $this->renderField('email', 'Email', 'email', $data['email'] ?? '', $errors);
        echo $this->renderField('password', 'Contraseña', 'password', '', $errors, ['minlength' => 8]);
        echo $this->renderField('confirm_password', 'Confirmar contraseña', 'password', '', $errors);

        echo '<div class="field">';
        echo '<label for="profile_image">Imagen de perfil</label>';
        echo '<input type="file" name="profile_image" id="profile_image" accept="image/*">';
        if (!empty($errors['profile_image'])) {
            echo '<div class="error">' . htmlspecialchars($errors['profile_image']) . '</div>';
        }
        echo '</div>';

        echo '<button type="submit" class="button">Registrar</button>';
        echo '</form>';
        echo '<p class="note">La contraseña debe tener al menos 8 caracteres.</p>';
        echo '</div>';
        echo '<script>
            const form = document.querySelector("form");
            form.addEventListener("submit", function (event) {
                const password = document.getElementById("password").value;
                const confirm = document.getElementById("confirm_password").value;

                if (password.length < 8) {
                    alert("La contraseña debe tener al menos 8 caracteres.");
                    event.preventDefault();
                    return;
                }

                if (password !== confirm) {
                    alert("Las contraseñas no coinciden.");
                    event.preventDefault();
                }
            });
        </script>';
        $this->renderFooter();
    }

    private function renderField(string $name, string $label, string $type, string $value, array $errors, array $attributes = []): string
    {
        $field = '<div class="field">';
        $field .= '<label for="' . htmlspecialchars($name) . '">' . htmlspecialchars($label) . '</label>';
        $attrString = '';
        foreach ($attributes as $key => $attrValue) {
            $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($attrValue) . '"';
        }
        $field .= '<input type="' . htmlspecialchars($type) . '" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '"' . $attrString . ' required>';
        if (!empty($errors[$name])) {
            $field .= '<div class="error">' . htmlspecialchars($errors[$name]) . '</div>';
        }
        $field .= '</div>';
        return $field;
    }
}
