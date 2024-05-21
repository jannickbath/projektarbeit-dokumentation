#!/bin/bash

# Kompilierung der Projektdokumentation

# PlantUML Diagramme kompilieren und in Anhang verschieben
read -p "Sollen die PlantUML Diagramme kompiliert werden (y/n)? " answer
if [ "$answer" == "y" ]; then
    echo "Die Diagramme werden kompiliert"
    echo "Diagramme werden in SVG umgewandelt"
    for file in ./PlantUML/*.puml; do
        java -jar plantuml.jar -charset UTF-8 -svg "$file"
    done

    echo "Diagramme wurden erfolgreich in SVG umgewandelt"
    echo "Diagramme werden in PDF umgewandelt"
    for svgFile in ./PlantUML/*.svg; do
        pdfName="./Anhang/$(basename "$svgFile" .svg).pdf"
        inkscape --export-filename="$pdfName" "$svgFile"
    done

    echo "Diagramme wurden erfolgreich in PDF umgewandelt"
    echo "Diagramme als SVG werden geloescht"
    rm ./PlantUML/*.svg
    echo "Diagramme als SVG wurden erfolgreich geloescht"
else
    echo "Die Diagramme werden nicht kompiliert"
fi

# Projektdokumentation.tex kompilieren
for i in {1..2}; do
    echo "Projektdokumentation.tex wird kompiliert"
    docker run --rm -v $(pwd):/data blang/latex pdflatex -interaction=nonstopmode Projektdokumentation.tex
    echo "Projektdokumentation.tex wurde kompiliert"
done

# Projektdokumentation öffnen
read -p "Soll die Datei geoeffnet werden (y/n)? " answer
if [ "$answer" == "y" ]; then
    xdg-open "./Projektdokumentation.pdf"
    echo "Datei wurde geoeffnet"
else
    echo "Script wird beendet"
fi
