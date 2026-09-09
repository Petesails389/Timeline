//go:build ignore

package main

import (
	"log"
	"net/http"
	"os"
	"text/template"
)

type Page struct {
	Name string
}

func renderTemplate(w http.ResponseWriter, path string, data *Page) {
	tmpl, err := template.New("").ParseFiles(path, "../html/main.html")
	if err != nil {
		panic(err.Error())
	}
	err = tmpl.ExecuteTemplate(w, "main", data)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}

func HTMLHandler(w http.ResponseWriter, r *http.Request) {
	page := r.URL.Path[len("/"):]
	if len(page) == 0 {
		page = "index.html"
	}

	path := "../html/" + page + ".html"
	if _, err := os.Stat(path); err != nil {
		http.Error(w, "404: Not found", http.StatusNotFound)
		return
	}
	renderTemplate(w, path, &Page{Name: page})
}

func GETHandler(w http.ResponseWriter, r *http.Request) {
	http.ServeFile(w, r, ".."+r.URL.Path)
}

func main() {
	http.HandleFunc("GET /", HTMLHandler)
	http.HandleFunc("GET /css/", GETHandler)
	http.HandleFunc("GET /js/", GETHandler)
	log.Fatal(http.ListenAndServe(":8080", nil))
}
