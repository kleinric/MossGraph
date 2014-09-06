all: docs

.PHONY: docs
docs:
	doxygen ./docs/Doxyfile
