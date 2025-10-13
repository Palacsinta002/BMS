#### Gyors és biztonságos pénzküldés blokkláncon

Tervezzetek egy **mobilos vagy webes megoldást**, amely lehetővé teszi a **gyors, egyszerű és biztonságos pénzküldést** egy publikus blokklánc-hálózaton. A rendszer központi eleme egy **új digitális pénz (token)**, amely a **forint értékéhez van rögzítve** (stablecoin jelleggel), így a felhasználók **valós értékhez kötött digitális vagyont** használhatnak.

###### A megoldás része

- Tokenek mint-burn mechanizmusa: FIAT számláról történő beutalás és visszavezetés kialakítása.
- Ehhez tartozó képernyők megtervezése (pl. feltöltés, visszaváltás, egyenleg).
- Biztonságos tranzakciók megjelenítése valós időben a blokkláncon.
- Mobilalkalmazás vagy webes felület a gyors és egyszerű pénzküldéshez (pl. peer-to-peer átutalás, QR-kód, kontaktlista integráció).
- Stabilitás biztosítása: a digitális pénz valóban a forint árfolyamát kövesse.

###### Mire térjetek ki a tervezésnél?

- Hogyan biztosítjátok a gyors és biztonságos pénzküldést?
- Hogyan történik a FIAT ↔ token átváltás és annak felhasználói élménye?
- Hogyan jelennek meg a tranzakciók a blokkláncon valós időben?
- Hogyan lesz a rendszer laikusok számára is érthető és könnyen kezelhető?

# Ötletek
#### ➤ a) FIAT oldalon

- A pénzmozgás jellemzően **PSD2 / Open Banking API-kon** vagy **banki integráción** keresztül történik.
- A rendszer **automatikusan egyezteti** a beérkezett utalásokat (pl. referenciaazonosító alapján).
- Biztonságot a **KYC/AML** folyamatok, **titkosított kommunikáció (TLS)**, **tokenizált banki adatok** és **engedélyezett fizetési szolgáltatók (pl. licensed EMI, PSP)** garantálják.

#### ➤ b) Blokklánc oldalon

- A tokenküldés egy **okosszerződés** segítségével történik (ERC-20, BEP-20, stb.), ami **transzparens** és **kriptográfiailag hitelesített**.
- A tranzakciók **néhány másodperc alatt** végbemennek (Layer-2 megoldásokkal, pl. Polygon, Arbitrum vagy zk-rollup esetén még gyorsabban).
- A biztonságot a **digitális aláírás (private key)** és a **blokklánc konszenzusmechanizmusa** adja.
