/* -*- Mode: C; indent-tabs-mode: t; c-basic-offset: 8; tab-width: 8 -*- */

/* 
 * gdk-pixbuf-convert.c gdk pixbuf replacement for convert geometry commands
 *
 * Copyright (C) 2003 Ximian Inc.
 *
 * Author: Larry Ewing <lewing@ximian.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 */

/* 
 * build with 
 * gcc `pkg-config --cflags gdk-pixbuf-2.0` gdk-pixbuf-convert.c -o gdk-pixbuf-convert `pkg-config --libs gdk-pixbuf-2.0`
 */

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <gdk-pixbuf/gdk-pixbuf.h>
#include <glib-object.h>

#define d(x) x

typedef enum {
	SAVE_JPG,
	SAVE_PNG
} OutputType;

GdkPixbuf *
scale (GdkPixbuf *src, char *geom)
{
	GdkPixbuf *dest = NULL;
	char *s, *e;
	guint w;
	guint h;
	guint src_w, dest_w;
	guint src_h, dest_h;
	double scale_w = 1.0;
	double scale_h = 1.0;
	gboolean force = FALSE;

	src_w = gdk_pixbuf_get_width (src);
	src_h = gdk_pixbuf_get_height (src);
	
	/* d(printf ("%d %d ", src_w, src_h);) */

	s = e = geom;
	
	w = strtol (s, &e, 10);
	/* d(printf ("%d ", w);) */
	if (e != s) {
		scale_w = w / (double)src_w;
	} else {
		w = 100;
	}
 		
	
	if (*e == 'x')
		e++;
	else 
		return NULL;
	
	s = e;
	
	h = strtol (s, &e, 10);
	/* d(printf ("%d ", h);) */
	if (e != s) {
		scale_h = h / (double)src_h;
	} else {
		h = 100;
	}

	if (*e == '%') {
		scale_h = h / 100.0;
		scale_w = w / 100.0;
		e++;
	}

	/* d(printf ("%f %f\n", scale_w, scale_h);) */
	
	while (*e) {
		switch (*e) {
		case '!':
			force = TRUE;
			break;
		case '>':
			if (scale_w >= 1.0 && scale_h >= 1.0)
				scale_w = scale_h = 1.0;		
			break;
		case '<':
			if (scale_w < 1.0 || scale_h <= 1.0)
				scale_w = scale_h = 1.0;
			break;
		default:
			break;
		}
		e++;
	}		
	
	if (!force) {
		/* Keep aspect ratio */
		scale_w = scale_h = MIN (scale_h, scale_w);
	}

	dest_w = (int)(src_w * scale_w + 0.5);
	dest_h = (int)(src_h * scale_h + 0.5);

	/* printf ("%dx%d\n", dest_w, dest_h); */

	/* dest = gdk_pixbuf_scale_simple (src, dest_w, dest_h, GDK_INTERP_BILINEAR);*/
	dest = gdk_pixbuf_scale_simple (src, dest_w, dest_h, GDK_INTERP_HYPER);

	return dest;
}

OutputType
get_filetype (char *filename)
{
	gint len = strlen (filename);
	
	if (len > 4 && g_ascii_strncasecmp (filename + len - 4, ".png", 4)== 0)
		return SAVE_PNG;
	else
		return SAVE_JPG;
}

int
convert (char *geom, char *quality, char *sfile, char *dfile)
{
	GdkPixbuf *src;
	GdkPixbuf *dest;
	GError *error = NULL;
	
	src = gdk_pixbuf_new_from_file (sfile, &error);
	
	if (!src) {
		fprintf (stderr, "Unable to open input file");
		return 1;
	}
	
	if (geom) {
		dest = scale (src, geom);
	} else {
		dest = src;
		g_object_ref (dest);
	}
	
	if (!dest)
		return 1;
	
	unlink (dfile);
	if (get_filetype (dfile) == SAVE_JPG)
		gdk_pixbuf_save (dest, dfile, "jpeg", &error,
				 "quality", quality, NULL);
	else
		gdk_pixbuf_save (dest, dfile, "png", &error, NULL);
	
	g_object_unref (src);
	g_object_unref (dest);
	
	if (error) {
		return 1;
	}
	
	return 0;
}

int
main (int argc, char **argv)
{
  	char *dfile;
	char *sfile;
	char *geom = NULL;
	char *quality = "100";
	
	g_type_init ();
	
	if (argc > 4) {
		int i = 1;
		if (!g_ascii_strcasecmp (argv[i], "-geometry")) {
			i++;
			geom = argv[i++];			
			if (argc < 5)
				goto usage;
		}
		if (!g_ascii_strcasecmp (argv[i], "-quality")) {
			i++;
			quality = argv[i++];
			if (argc < 7)
				goto usage;
			if ((atoi(quality) < 0) || (atoi(quality) > 100)) {
				g_print ("Value for '-quality' must be in range 0-100!\n");
				goto usage;
			}
		}
		sfile = argv[i++];
		dfile = argv[i++];
	} else {
		goto usage;
	}
	
	return convert (geom, quality, sfile, dfile);
	
 usage:
	g_print ("Usage:\n");
	g_print ("   gdk-pixbuf-convert -geometry <X>x<Y> [-quality <0-100>] <input> <output>\n");
	g_print ("\n");
	g_print ("Example:\n");
	g_print ("   gdk-pixbuf-convert -geometry 120x120 -quality 75 foo.jpg bar.jpg\n");
	exit (2);
}
