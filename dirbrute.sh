#!/bin/bash
# ===========================================
# DIRECTORY BRUTEFORCE SCRIPT
# SAE Cybersécurité - AutoMarket
# ===========================================

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Colo

# Bannière
echo -e "${CYAN}"
echo "╔═══════════════════════════════════════════╗"
echo "║     DIRECTORY BRUTEFORCE TOOL             ║"
echo "║     SAE Cybersécurité - AutoMarket        ║"
echo "╚═══════════════════════════════════════════╝"
echo -e "${NC}"

# Vérification des arguments
if [ $# -lt 1 ]; then
    echo -e "${YELLOW}Usage: $0 <URL_CIBLE> [WORDLIST]${NC}"
    echo ""
    echo "Exemples:"
    echo "  $0 http://localhost/SAE-Cyber"
    echo "  $0 http://192.168.1.10/SAE-Cyber wordlist.txt"
    echo ""
    exit 1
fi

TARGET_URL=$1
WORDLIST=${2:-"wordlist.txt"}

# Vérifier que curl est installé
if ! command -v curl &> /dev/null; then
    echo -e "${RED}[ERREUR] curl n'est pas installé. Installez-le avec: sudo apt install curl${NC}"
    exit 1
fi

# Vérifier que la wordlist existe
if [ ! -f "$WORDLIST" ]; then
    echo -e "${RED}[ERREUR] Wordlist '$WORDLIST' non trouvée.${NC}"
    exit 1
fi

echo -e "${CYAN}[*] Cible       : $TARGET_URL${NC}"
echo -e "${CYAN}[*] Wordlist    : $WORDLIST${NC}"
echo -e "${CYAN}[*] Démarrage du scan...${NC}"
echo ""

FOUND=0
TOTAL=$(wc -l < "$WORDLIST")
COUNT=0

# Parcourir chaque ligne de la wordlist
while IFS= read -r DIR || [ -n "$DIR" ]; do
    # Ignorer les lignes vides et commentaires
    [[ -z "$DIR" || "$DIR" =~ ^# ]] && continue
    
    COUNT=$((COUNT + 1))
    URL="${TARGET_URL}/${DIR}"
    
    # Faire la requête HTTP et récupérer le code de réponse
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "$URL")
    
    # Afficher la progression
    printf "\r${CYAN}[*] Progression: %d/%d - Test: %-30s${NC}" "$COUNT" "$TOTAL" "$DIR"
    
    # Vérifier le code de réponse
    case $HTTP_CODE in
        200)
            echo -e "\n${GREEN}[+] TROUVÉ (200) : $URL${NC}"
            FOUND=$((FOUND + 1))
            ;;
        301|302)
            echo -e "\n${YELLOW}[~] REDIRECTION ($HTTP_CODE) : $URL${NC}"
            FOUND=$((FOUND + 1))
            ;;
        403)
            echo -e "\n${YELLOW}[!] INTERDIT (403) : $URL${NC}"
            FOUND=$((FOUND + 1))
            ;;
    esac
    
done < "$WORDLIST"

echo ""
echo ""
echo -e "${CYAN}═══════════════════════════════════════════${NC}"
echo -e "${GREEN}[✓] Scan terminé !${NC}"
echo -e "${CYAN}[*] Répertoires/fichiers trouvés : $FOUND${NC}"
echo -e "${CYAN}═══════════════════════════════════════════${NC}"
