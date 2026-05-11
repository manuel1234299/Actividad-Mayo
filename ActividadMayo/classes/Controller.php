<?php

class Controller
{
    private string $usersFile;
    private array $users = [];

    public function __construct(string $usersFile)
    {
        $this->usersFile = $usersFile;
        $this->loadData();
    }

    private function loadData(): void
    {
        if (!file_exists($this->usersFile)) {
            $this->users = [];
            return;
        }

        $content = file_get_contents($this->usersFile);
        $this->users = $content ? json_decode($content, true) ?? [] : [];
    }

    private function saveUsers(): void
    {
        if (!is_dir(dirname($this->usersFile))) {
            mkdir(dirname($this->usersFile), 0755, true);
        }

        file_put_contents($this->usersFile, json_encode($this->users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function validateRegisterData(array $data): array
    {
        $errors = [];
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if ($name === '') {
            $errors['name'] = 'El nombre es obligatorio.';
        }

        if ($email === '') {
            $errors['email'] = 'El email es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no tiene un formato válido.';
        } elseif ($this->findUserByEmail($email) !== null) {
            $errors['email'] = 'Ya existe un usuario con este email.';
        }

        if ($password === '') {
            $errors['password'] = 'La contraseña es obligatoria.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'La confirmación de la contraseña es obligatoria.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Las contraseñas no coinciden.';
        }

        return $errors;
    }

    private function findUserByEmail(string $email): ?array
    {
        foreach ($this->users as $user) {
            if (strcasecmp($user['email'], $email) === 0) {
                return $user;
            }
        }

        return null;
    }

    private function nextUserId(): int
    {
        if (empty($this->users)) {
            return 1;
        }

        $ids = array_column($this->users, 'id');
        return max($ids) + 1;
    }

    private function processProfileImage(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Error al subir la imagen.'];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            return ['error' => 'Formato de imagen no válido. Usa JPG, PNG o GIF.'];
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return ['error' => 'La imagen debe pesar menos de 2MB.'];
        }

        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('profile_', true) . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['error' => 'No se pudo guardar la imagen de perfil.'];
        }

        return ['path' => 'uploads/' . $filename];
    }

    public function register(array $data, array $files = []): array
    {
        $errors = $this->validateRegisterData($data);
        $profileImage = '';

        if (!empty($files['profile_image']['name'])) {
            $uploadResult = $this->processProfileImage($files['profile_image']);
            if (isset($uploadResult['error'])) {
                $errors['profile_image'] = $uploadResult['error'];
            } else {
                $profileImage = $uploadResult['path'];
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $user = [
            'id' => $this->nextUserId(),
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'password' => $this->hashPassword($data['password']),
            'profile_image' => $profileImage,
            'created_at' => date('c'),
        ];

        $this->users[] = $user;
        $this->saveUsers();

        return ['success' => true, 'user' => $user];
    }
}
