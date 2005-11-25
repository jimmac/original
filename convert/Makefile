PREFIX = /usr/local

GTKCFLAGS = `pkg-config --cflags gtk+-2.0`
GTKLDFLAGS += `pkg-config --libs gtk+-2.0`
GDKPIXBUFCFLAGS = `pkg-config --cflags gdk-pixbuf-2.0`
GDKPIXBUFLDFLAGS += `pkg-config --libs gdk-pixbuf-2.0`

CPPFLAGS = $(GTKCFLAGS) -D_GNU_SOURCE 
ifeq ($(DEBUG),yes)
CFLAGS += -O2 -g
LDFLAGS = -g #-lefence
else
CFLAGS += -Os -fomit-frame-pointer
endif
CFLAGS += -Wall
CPPFLAGS += -DPACKAGE=\"$(PACKAGE)\" -DPREFIX=\"$(PREFIX)\" -DPACKAGE_LOCALE_DIR=\"$(PREFIX)/share/locale\"
CFLAGS += -MD

MEMBERS = gdk-pixbuf-convert
LIB_MEMBERS =

OBJS = $(patsubst %,%.o,$(MEMBERS))
SOURCES = $(patsubst %,%.c,$(MEMBERS) $(LIB_MEMBERS))
LIB_OBJS = $(patsubst %,%.o,$(LIB_MEMBERS))
LIB_SOURCES = $(patsubst %,%.c,$(LIB_MEMBERS))

DEPS = $(patsubst %,%.d,$(MEMBERS) $(LIB_MEMBERS))


all: gdk-pixbuf-convert

gdk-pixbuf-convert: $(OBJS)
	$(CC) -o $@ $^  $(GDKPIXBUFCFLAGS) $(GDKPIXBUFLDFLAGS) $(LDFLAGS) -L.

clean:
	rm -f $(MEMBERS) $(OBJS) $(DEPS)

install: all 
	mkdir -p $(PREFIX)/bin
	install -s $(MEMBERS) $(PREFIX)/bin/

uninstall:
	$(RM) $(PREFIX)/bin/$(MEMBERS)

