#!/bin/bash

# Crée un répertoire principal
mkdir -p exo_monolog
cd exo_monolog || exit

# Task 1 : version exacte 2.3.0
mkdir -p task1
cat > task1/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": "2.3.0"
    }
}
EOF

# Task 2 : >2.2.0 <=2.3.5
mkdir -p task2
cat > task2/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": ">2.2.0 <=2.3.5"
    }
}
EOF

# Task 3 : >=2.1.0 <=2.2.0
mkdir -p task3
cat > task3/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": ">=2.1.0 <=2.2.0"
    }
}
EOF

# Task 4 : >=2.9.0 <2.9.2
mkdir -p task4
cat > task4/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": ">=2.9.0 <2.9.2"
    }
}
EOF

# Task 5 : >2.0.0 <2.3.5
mkdir -p task5
cat > task5/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": ">2.0.0 <2.3.5"
    }
}
EOF

echo "✅ Tous les fichiers composer.json ont été créés dans exo_monolog/"
echo "➡️  Va dans chaque dossier (task1..task5) et lance : composer install"
