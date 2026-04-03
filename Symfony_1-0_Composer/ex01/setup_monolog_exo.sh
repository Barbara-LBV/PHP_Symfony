#!/bin/bash

# Task 1 : version exacte 2.3.0
mkdir -p task1
cat > task1/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": "2.3.0"
    }
}
EOF
cd task1 && composer install && cd ..

# Task 2 : >2.2.0 <=2.3.5
mkdir -p task2
cat > task2/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": ">2.2.0 <=2.3.5"
    }
}
EOF
cd task2 && composer install && cd ..
# Task 3 : >=2.1.0 <=2.2.0
mkdir -p task3
cat > task3/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": ">=2.1.0 <=2.2.0"
    }
}
EOF
cd task3 && composer install && cd ..


# Task 4 : >=2.9.0 <2.9.2
mkdir -p task4
cat > task4/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": ">=2.9.0 <2.9.2"
    }
}
EOF
cd task4 && composer install && cd ..


# Task 5 : >2.0.0 <2.3.5
mkdir -p task5
cat > task5/composer.json <<'EOF'
{
    "require": {
        "monolog/monolog": ">2.0.0 <2.3.5"
    }
}
EOF
cd task5 && composer install && cd ..

echo "✅ Tous les fichiers composer.json ont été créés dans task#"
echo "✅ Tous les packages ont été installés avec composer install dans chaque dossier task#"
