//go:build ignore

package main

import (
	"log"
	"net/http"
	"os"
	"strings"
	"text/template"
)

type Page struct {
	Name          string
	ActiveSession string
	TabFocus      string
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
	lastIndex := strings.LastIndex(r.URL.Path, "/")
	page := r.URL.Path[lastIndex+1:]
	path := r.URL.Path[1 : lastIndex+1]

	if len(page) == 0 {
		page = "index"
	}

	path = "../html/" + path + page + ".html"
	if _, err := os.Stat(path); err != nil {
		http.Error(w, "404: Not found", http.StatusNotFound)
		return
	}

	tabFocus := r.URL.Query().Get("focus")

	renderTemplate(w, path, &Page{Name: page, ActiveSession: "true", TabFocus: tabFocus})
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
