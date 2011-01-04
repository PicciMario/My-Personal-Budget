# My Personal Budget
My Personal Budget è una applicazione web based per la gestione della contabilità personale o per piccole imprese. 

## Funzioni
* Conti: possibilità di aprire un numero arbitrario di conti, intesi come raggruppamenti superiori di movimenti economici. Per ogni conto in ciascun momento è mostrato il saldo al giorno corrente.
* Chiusure contabili: a fine mese un conto può essere "chiuso", cristallizzando il saldo per evitare alterazioni successive.
* Movimenti: vengono mostrati i movimenti (entrate/uscite) del conto attivo. Vengono inoltre mostrati il saldo iniziale (corrispondente alla chiusura contabile del mese precedente), il saldo al giorno corrente, il saldo previsto a fine mese. Inoltre sono mostrate tutte le chiusure contabili dei mesi precedenti fino all'inizio dell'anno solare.
* Categorie, tag: i movimenti sono suddivisi in categorie contabili; inoltre a ciascun movimento può essere assegnato un numero arbitrario di tag liberi per facilitarne la classificazione. 

## Grafici
Sono previsti dei grafici per mostrare in modo chiaro ed immediato le situazioni finanziarie:

* Saldi mensili: un grafico che mostra le chiusure mensili di tutti i conti per tutto un anno solare.
* Saldi per categoria: un grafico che mostra la somma dei movimenti di ciascuna categoria (per tutti i conti) in un mese selezionato.
* Entrate/uscite: due grafici a torta che mostrano la distribuzione degli importi di entrate e uscite (di tutti i conti) tra le categorie.

# Installazione

## Prerequisiti
* Un server web (Apache2, Lighttpd, ...) con PHP5 e supporto SQLite3.

## Generazione database
Il database è SqLite3 ed è nel file my_database.db (che fantasia, eh?). E' stato previsto un file di comandi SQL "createdb.sql" per costruire il database e inserire dei dati di prova. E' sufficiente da terminale spostarsi nella directory root del sito e:

	sqlite3 my_database.db
	.read createdb.sql
	.exit

(se il database esiste già le tabelle vengono droppate e ricostruite).

## Dati di prova

Tra i dati di prova vengono generati due utenti:

* user: "mario", password: "mille"
* user: "admin", password: "mille"
